<?php

namespace App\Controller;

use App\Entity\User;
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
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */

        $user->setName($request->request->get('name'));
        $user->setEmail($request->request->get('email'));

        $profile = $user->getSupplierProfile();

        if (in_array('ROLE_SUPPLIER', $user->getRoles()) && $profile !== null) {
            $profile->setPhoneNumber($request->request->get('phone_number'));
            $profile->setAddress($request->request->get('address'));
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_profile');
    }
}
