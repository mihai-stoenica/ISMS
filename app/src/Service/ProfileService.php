<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class ProfileService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ){}

    public function editProfile(User $user, Request $request) : ?string
    {
        if($message = $this->validateProfileInput($user, $request)){
            return $message;
        }
        $user->setName($request->request->get('name'));
        $user->setEmail($request->request->get('email'));

        $profile = $user->getSupplierProfile();

        if (in_array('ROLE_SUPPLIER', $user->getRoles()) && $profile !== null) {
            $profile->setPhoneNumber($request->request->get('phone_number'));
            $profile->setAddress($request->request->get('address'));

        }

        $this->entityManager->flush();
        return null;

    }
    private function validateProfileInput(User $user, Request $request): ?string
    {
        $name = trim((string) $request->request->get('name'));
        $email = trim((string) $request->request->get('email'));

        if ($name === '' || strlen($name) > 255) {
            return 'The name cannot be blank and not exceed 255 characters.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'The Address is not valid.';
        }

        if (in_array('ROLE_SUPPLIER', $user->getRoles()) && $user->getSupplierProfile() !== null) {
            $phoneNumber = trim((string) $request->request->get('phone_number'));
            $address = trim((string) $request->request->get('address'));

            if ($phoneNumber === '' || strlen($phoneNumber) != 10) {
                return 'The phone number must be 10 characters long.';
            }

            if ($address === ''|| strlen($address) >255) {
                return 'The address must not be empty and not exceed 255 characters..';
            }
        }

        return null;
    }


}
