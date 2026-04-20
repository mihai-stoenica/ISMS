<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Enum\TaskStatus;
use App\Form\CreateTaskType;
use App\Repository\TaskRepository;
use App\Service\TaskService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class TaskController extends AbstractController
{
    #[Route('/task', name: 'app_task')]
    #[IsGranted('ROLE_MANAGER')]
    #[IsGranted('ROLE_STAFF')]
    public function index(
        TaskRepository $taskRepository,
        Request $request,
        TaskService $taskService,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $status = $request->query->get('status');
        $search = $request->query->get('search');
        $order = $request->query->get('order');

        $user = $this->getUser();

        $form = null;

        if($this->isGranted('ROLE_MANAGER')) {

            $newTask = new Task();
            $form = $this->createForm(CreateTaskType::class, $newTask);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $productTasks = $newTask->getProduct()->getTasks();

                $isProductBusy = $productTasks->exists(function (int $key, Task $task)  {
                   return $task->getStatus() === TaskStatus::ASSIGNED
                       || $task->getStatus() === TaskStatus::PENDING;
                });

                if($isProductBusy) {
                    $this->addFlash('error', "This product is already assigned to a task");

                    return $this->redirectToRoute('app_task');
                }

                if($newTask->getDestination() == $newTask->getProduct()->getLocation()) {
                    $this->addFlash('error', "The destination must be different from the source");

                    return $this->redirectToRoute('app_task');
                }

                if(!in_array('ROLE_STAFF', $newTask->getEmployee()->getRoles())) {
                    $this->addFlash('error', "You can only assign tasks to a staff member");

                    return $this->redirectToRoute('app_task');
                }

                $newTask->setManager($user);
                $newTask->setStatus(TaskStatus::ASSIGNED);
                $newTask->setCreatedAt(new \DateTimeImmutable());

                $newTask->setSource($newTask->getProduct()->getLocation());

                $entityManager->persist($newTask);
                $entityManager->flush();

                return $this->redirectToRoute('app_task');
            }

            $tasks = $taskRepository->findBySearchParams($status, $search);
        } else {
            $tasks = $taskRepository->findBySearchParams($status, $search, $order, $user);
        }

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
            ...($form ? ['form' => $form->createView()] : []),
        ]);
    }

    #[Route('/task/accept/{id}', name: 'app_task_accept', methods: ['POST'])]
    #[IsGranted('ROLE_STAFF')]
    public function accept(
        Task $task,
        TaskService $taskService
    ) : Response {

        /** @var User $user */
        $user = $this->getUser();

        $taskService->acceptTask($task, $user);

        return $this->redirectToRoute('app_task');
    }

    #[Route('/task/complete/{id}', name: 'app_task_complete', methods: ['POST'])]
    #[IsGranted('ROLE_STAFF')]
    public function complete(
        Task $task,
        TaskService $taskService
    ) : Response {

        /** @var User $user */
        $user = $this->getUser();

        $taskService->completeTask($task, $user);

        return $this->redirectToRoute('app_task');
    }
}
