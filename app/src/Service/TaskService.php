<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\Task;
use App\Entity\User;
use App\Enum\Location;
use App\Enum\TaskStatus;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class TaskService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
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
        $task->setCompletedAt(new DateTimeImmutable());
        $task->getProduct()->setLocation($task->getDestination());
        $this->entityManager->persist($task);
        $this->entityManager->flush();
    }

    private function isLocationEmpty(Location $location) : bool
    {
        $productCount = $this->entityManager
            ->getRepository(Product::class)
            ->count([
                'location' => $location
            ]);

        $taskCount = $this->entityManager
            ->getRepository(Task::class)
            ->count([
                'destination' => $location,
                'status' => [TaskStatus::PENDING, TaskStatus::ASSIGNED],
            ]);

        return ($productCount == 0 && $taskCount == 0);
    }

    public function getErrorMessage(Task $newTask) : ?string
    {
        $productTasks = $newTask->getProduct()->getTasks();

        $isProductBusy = $productTasks->exists(function (int $key, Task $task)  {
            return $task->getStatus() === TaskStatus::ASSIGNED
                || $task->getStatus() === TaskStatus::PENDING;
        });

        if($isProductBusy) {
            return "This product is already assigned to a task";
        }

        if($newTask->getDestination() == $newTask->getProduct()->getLocation()) {
            return "The destination must be different from the source";
        }

        if(!in_array('ROLE_STAFF', $newTask->getEmployee()->getRoles())) {
            return "You can only assign tasks to a staff member";
        }

        if(!$this->isLocationEmpty($newTask->getDestination())) {
            return "This location is already in use";
        }

        return null;
    }

    public function mapTask(Task $newTask, User $user) : void
    {
        $newTask->setManager($user);
        $newTask->setStatus(TaskStatus::ASSIGNED);
        $newTask->setCreatedAt(new DateTimeImmutable());
        $newTask->setSource($newTask->getProduct()->getLocation());

        $this->entityManager->persist($newTask);
        $this->entityManager->flush();
    }

    public function sendTaskAssignedEmail(Task $task) : void
    {
        if(!$task->getEmployee() || !$task->getEmployee()->getEmail()) {
            return;
        }

        $email = new TemplatedEmail()
            ->from('warehouse@demomailtrap.co')
            ->to($task->getEmployee()->getEmail())
            ->subject('New task assigned: ' . $task->getProduct()->getName())
            ->htmlTemplate('email/task_assigned.html.twig')
            ->context([
                'task' => $task,
            ]);

        $this->mailer->send($email);

    }
}
