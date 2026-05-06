<?php

namespace App\Controller;

use App\Entity\Review;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, EntityManagerInterface $em, ReviewRepository $reviewRepo): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$user->isAccepted()) {
            return $this->render('home/pending.html.twig');
        }

        if ($request->isMethod('POST') && $request->request->has('review_content')) {
            $review = new Review();
            $review->setContent($request->request->get('review_content'));
            $review->setRating($request->request->getInt('rating', 5));
            $review->setAuthor($user); // Set the User entity object
            $review->setCreatedAt(new \DateTimeImmutable());

            $em->persist($review);
            $em->flush();

            $this->addFlash('success', 'Review posted to the warehouse wall!');

            return $this->redirectToRoute('app_home');
        }

        $reviews = $reviewRepo->findBy([], ['createdAt' => 'DESC']);

        return $this->render('home/index.html.twig', [
            'warehouse_name' => 'WareBros Central',
            'description'    => 'Our warehouse is a state-of-the-art facility specializing in high-volume inventory management and rapid supplier distribution.',
            'address'        => '123 Logistics Way, Sector 4, Bucharest',
            'contact'        => '+40 722 000 000',
            'working_hours'  => '08:00 - 20:00 (Mon-Fri)',
            'reviews'        => $reviews
        ]);
    }
}
