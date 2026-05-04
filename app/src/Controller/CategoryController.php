<?php

namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
    #[Route('/category/create', name: 'app_category_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $name = $request->request->get('category_name');

        if($name){
            $category = new Category();
            $category->setName($name);
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Category created!');
        }

        return $this->redirectToRoute('app_products_catalog');
    }
}
