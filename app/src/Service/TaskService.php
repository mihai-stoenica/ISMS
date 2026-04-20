<?php

namespace App\Service;

use App\Entity\Task;
use App\Entity\User;
use App\Enum\TaskStatus;
use Doctrine\ORM\EntityManagerInterface;

class TaskService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function acceptTask(Task $task, User $user) : void {

        if($task->getEmployee() !== $user || $task->getStatus() !== TaskStatus::ASSIGNED) {
            return;
        }

        $task->setStatus(TaskStatus::PENDING);
        $this->entityManager->persist($task);
        $this->entityManager->flush();
    }

    public function completeTask(Task $task, User $user) : void
    {
        if($task->getStatus() !== TaskStatus::PENDING || $task->getEmployee() !== $user) {
            return;
        }
        $task->setStatus(TaskStatus::COMPLETED);
        $task->setCompletedAt(new \DateTimeImmutable());
        $task->getProduct()->setLocation($task->getDestination());
        $this->entityManager->persist($task);
        $this->entityManager->flush();
    }
}
