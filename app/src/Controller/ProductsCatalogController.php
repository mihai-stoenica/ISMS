<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ProductsCatalogController extends AbstractController
{
    #[Route('/products/catalog', name: 'app_products_catalog')]
    #[IsGranted(new Expression('is_granted("ROLE_MANAGER") or is_granted("ROLE_SUPPLIER")'))]
    public function index(
        ProductRepository $productRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $search = $request->query->get('search');

        $products = $productRepository->findBySearch($search);

        $form = null;

        if($this->isGranted('ROLE_MANAGER')) {
            $product = new Product();
            $form = $this->createForm(ProductType::class, $product);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {
                $product->setCurrentStock(0);
                $entityManager->persist($product);
                $entityManager->flush();

                $this->addFlash('success', 'Product added successfully!');

                return $this->redirectToRoute('app_products_catalog');
            }
        }

        return $this->render('products_catalog/index.html.twig', [
           'products' => $products,
            ...($form ? ['form' => $form->createView()] : []),
        ]);
    }

}
