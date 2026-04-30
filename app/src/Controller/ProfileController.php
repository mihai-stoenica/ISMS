<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\ProfileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile', methods: ['GET'])]
    public function showProfile(
        UserInterface $user
    ): Response
    {
        return $this->render('profile/index.html.twig', [
            'user_data' => $user
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit', methods: ['POST'])]
    public function editProfile(
        Request $request,
        UserInterface $user,
        ProfileService $profileService
    ): Response {

        /** @var User $user */

        if($message = $profileService->editProfile($user, $request)){
            $this->addFlash('error',$message);
            return $this->redirectToRoute('app_profile');
        }

        return $this->redirectToRoute('app_profile');
    }
}
