<?php

namespace App\Controller;

use App\Entity\SupplierProfile;
use App\Entity\User;
use App\Form\RegistrationType;
use App\Form\SupplierRegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(
        AuthenticationUtils $authenticationUtils
    ): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        $this->redirect('app_home');
    }

    #[Route('/register', name: 'app_register_staff')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em,
        Security $security,
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($hasher->hashPassword($user, $form->get('plainPassword')->getData()));
            $user->setRoles(['ROLE_STAFF_PENDING']);
            $user->setIsAccepted(false);

            $em->persist($user);
            $em->flush();
            return $security->login($user, 'form_login');
        }

        return $this->render('security/register.html.twig', ['registrationForm' => $form]);
    }

    #[Route(path: '/register/supplier', name: 'app_register_supplier')]
    public function registerSupplier(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em,
        Security $security
    ) : Response
    {
        $supplierProfile = new SupplierProfile();
        $form = $this->createForm(SupplierRegistrationType::class, $supplierProfile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \App\Entity\User $user */
            $user = $supplierProfile->getUser();

            $plainPassword = $form->get('user')->get('plainPassword')->getData();
            $user->setPassword($hasher->hashPassword($user, $plainPassword));

            $user->setRoles(['ROLE_SUPPLIER_PENDING']);
            $user->setIsAccepted(false);

            $supplierProfile->setUser($user);

            $em->persist($user);
            $em->persist($supplierProfile);
            $em->flush();

            return $security->login($user, 'form_login');
        }

        return $this->render('security/register_supplier.html.twig', [
            'registrationForm' => $form->createView()
        ]);
    }
}
