<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\StaffService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class StaffController extends AbstractController
{
    #[Route('/staff', name: 'app_staff')]
    #[IsGranted('ROLE_MANAGER')]
    public function index(
        UserRepository $userRepository,
        Request $request,
    ): Response
    {
        $status = $request->query->get('status');
        $type = $request->query->get('type');
        $search = $request->query->get('search');

        $users = $userRepository->findBySearchParams($status, $type, $search);

        return $this->render('staff/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/staff/promote/{id}', name: 'app_staff_promote', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function promote(
        User $user,
        StaffService $staffService,
    ) : Response
    {
        $staffService->promote($user);
        $this->addFlash('success', 'Staff member ' . $user->getName() . ' has been promoted to Manager.');

        return $this->redirectToRoute('app_staff');
    }

    #[Route('/staff/demote/{id}', name: 'app_staff_demote', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function demote(
        User $user,
        StaffService $staffService,
    ) : Response
    {
        $staffService->demote($user);
        $this->addFlash('success', 'Staff member ' . $user->getName() . ' has been demoted to Staff.');

        return $this->redirectToRoute('app_staff');
    }

    #[Route('/staff/accept/{id}', name: 'app_staff_accept', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function accept(
        User $user,
        StaffService $staffService,
    ) : Response
    {
        $staffService->accept($user);

        $this->addFlash('success', 'Staff member ' . $user->getName() . ' has been accepted.');

        return $this->redirectToRoute('app_staff');
    }

    #[Route('/staff/reject/{id}', name: 'app_staff_reject', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function reject(
        User $user,
        StaffService $staffService,
    ) : Response
    {
        $staffService->reject($user);

        $this->addFlash('success', 'Staff member ' . $user->getName() . ' has been rejected.');

        return $this->redirectToRoute('app_staff');
    }
}
