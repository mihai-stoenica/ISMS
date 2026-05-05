<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Enum\Location;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SupplierProductRepository;
use App\Service\ProductsCatalogService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
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
        EntityManagerInterface $entityManager,
        PaginatorInterface $paginator,
        CategoryRepository $categoryRepository,
    ): Response
    {

        $search = $request->query->get('search');
        $categoryId = $request->query->get('categoryId');

        $query = $productRepository->findBySearch($search, $categoryId);
        $categories = $categoryRepository->findAll();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            7
        );

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
           'products' => $pagination,
            'categories' => $categories,
            ...($form ? ['form' => $form->createView()] : []),
        ]);
    }

    #[Route('/products/catalog/sell/{id}', name: 'app_products_catalog_sell')]
    #[IsGranted('ROLE_SUPPLIER')]
    public function supplierSell(
        Product $product,
        Request $request,
        ProductsCatalogService $productsCatalogService,
    ) : Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $price = $request->request->get('price');

        if($message = $productsCatalogService->mapSupplierProduct($user, $product, $price)) {
            $this->addFlash('error', $message);
        }
        return $this->redirectToRoute('app_products_catalog');
    }

    #[Route('/products/catalog/unsell/{id}', name: 'app_products_catalog_unsell')]
    #[IsGranted('ROLE_SUPPLIER')]
    public function supplierUnsell(
        Product $product,
        ProductsCatalogService $productsCatalogService,
    ) : Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if($message = $productsCatalogService->unsellProduct($user, $product)) {
            $this->addFlash('error', $message);
        }

        return $this->redirectToRoute('app_products_catalog');
    }

    #[Route('/products/catalog/buy/{id}', name: 'app_products_catalog_buy')]
    #[IsGranted('ROLE_MANAGER')]
    public function managerBuy(
        Product $product,
        SupplierProductRepository $supplierProductRepository,
    ) : Response
    {
        $sellers = $supplierProductRepository->findSellersForProduct($product);
        $ramps = array_filter(Location::cases(), fn(Location $loc) => $loc->isRamp());

        return $this->render('products_catalog/buy.html.twig', [
            'product' => $product,
            'sellers' => $sellers,
            'ramps' => $ramps,

        ]);
    }

}
