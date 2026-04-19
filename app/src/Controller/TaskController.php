<?php

namespace App\Controller;

use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class TaskController extends AbstractController
{
    #[Route('/task', name: 'app_task')]
    #[IsGranted('ROLE_USER')]
    public function index(
        TaskRepository $taskRepository,
    ): Response
    {
        $user = $this->getUser();

        if($this->isGranted('ROLE_MANAGER')) {
            $tasks = $taskRepository->findAll();
        } else {
            $tasks = $user->getAssignedTasks();
        }

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }
}
