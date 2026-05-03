<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Enum\TaskStatus;
use App\Form\CreateTaskType;
use App\Repository\ProductRepository;
use App\Repository\TaskRepository;
use App\Service\TaskService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class TaskController extends AbstractController
{
    #[Route('/task', name: 'app_task')]
    #[IsGranted(new Expression('is_granted("ROLE_MANAGER") or is_granted("ROLE_STAFF")'))]
    public function index(
        TaskRepository $taskRepository,
        Request $request,
        TaskService $taskService,
        ProductRepository $productRepository,
    ): Response
    {
        $status = $request->query->get('status');
        $search = $request->query->get('search');
        $order = $request->query->get('order');

        /** @var User $user */
        $user = $this->getUser();

        $form = null;
        $selectedProductId = $request->query->get('productId');

        if($this->isGranted('ROLE_MANAGER')) {

            $newTask = new Task();
            if($selectedProductId) {
                $product = $productRepository->find($selectedProductId);
                $newTask->setProduct($product);
            }
            $form = $this->createForm(CreateTaskType::class, $newTask);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                if($message = $taskService->getErrorMessage($newTask)) {
                    $this->addFlash('error', $message);

                    return $this->redirectToRoute('app_task');
                }

                $taskService->mapTask($newTask, $user);

                $taskService->sendTaskAssignedEmail($newTask);

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
