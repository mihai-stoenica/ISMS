<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\ProductOrder;
use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class OrderController extends AbstractController
{

    #[Route('/cart/add/{id}', name: 'cart_add', methods: ['POST'])]
    #[IsGranted('ROLE_SUPPLIER')]
    public function add(int $id, Request $request, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Product not found.');
        }

        $session = $request->getSession();
        $cart = $session->get('cart', []);
        $quantity = $request->request->getInt('quantity', 1);

        if ($quantity > $product->getCurrentStock()) {
            $this->addFlash('error', "Only " . $product->getCurrentStock() . " units available in stock.");
            return $this->redirectToRoute('app_products_catalog');
        }

        if (isset($cart[$id])) {
            $cart[$id] += $quantity;
        } else {
            $cart[$id] = $quantity;
        }

        $session->set('cart', $cart);
        $this->addFlash('success', "{$product->getName()} added to cart!");

        return $this->redirectToRoute('app_products_catalog');
    }

    #[Route('/cart', name: 'app_cart')]
    #[IsGranted('ROLE_SUPPLIER')]
    public function showCart(Request $request, ProductRepository $productRepository): Response
    {
        $cart = $request->getSession()->get('cart', []);
        $cartData = [];
        $total = 0;

        foreach ($cart as $id => $quantity) {
            $product = $productRepository->find($id);
            if ($product) {
                $subtotal = $product->getSellingPrice() * $quantity;
                $cartData[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal
                ];
                $total += $subtotal;
            }
        }

        return $this->render('order/index.html.twig', [
            'items' => $cartData,
            'total' => $total,
        ]);
    }

    #[Route('/cart/checkout', name: 'app_cart_checkout', methods: ['POST'])]
    #[IsGranted('ROLE_SUPPLIER')]
    public function checkout(Request $request, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        if (empty($cart)) {
            $this->addFlash('error', 'Your cart is empty.');
            return $this->redirectToRoute('app_products_catalog');
        }

        $order = new Order();
        $order->setSupplier($this->getUser()->getSupplierProfile());
        $order->setStatus(OrderStatus::PENDING);

        $totalPrice = 0;

        foreach ($cart as $id => $quantity) {
            $product = $productRepository->find($id);
            if (!$product) continue;

            $productOrder = new ProductOrder();
            $productOrder->setProduct($product);
            $productOrder->setQuantity($quantity);
            $productOrder->setOrder($order);

            $totalPrice += ($product->getSellingPrice() * $quantity);
            $em->persist($productOrder);
        }

        $order->setTotalPrice((string)$totalPrice);
        $em->persist($order);
        $em->flush();

        $session->remove('cart');
        $this->addFlash('success', 'Order placed successfully! Pending manager approval.');

        return $this->redirectToRoute('app_products_catalog');
    }

    #[Route('/manager/orders', name: 'app_manager_orders')]
    #[IsGranted('ROLE_MANAGER')]
    public function manageOrders(OrderRepository $orderRepo): Response
    {
        return $this->render('order/manager_list.html.twig', [
            'orders' => $orderRepo->findBy(['status' => OrderStatus::PENDING], ['id' => 'DESC'])
        ]);
    }


    #[Route('/manager/order/{id}/approve', name: 'app_order_approve', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function approveOrder(Order $order, EntityManagerInterface $em): Response
    {
        if ($order->getStatus() !== OrderStatus::PENDING) {
            $this->addFlash('error', 'This order has already been processed.');
            return $this->redirectToRoute('app_manager_orders');
        }

        foreach ($order->getProductOrders() as $item) {
            $product = $item->getProduct();
            $requestedQty = $item->getQuantity();


            if ($product->getCurrentStock() < $requestedQty) {
                $this->addFlash('error', "Stock alert: Only {$product->getCurrentStock()} left for {$product->getName()}.");
                return $this->redirectToRoute('app_manager_orders');
            }

            $product->setCurrentStock($product->getCurrentStock() - $requestedQty);
            if ($product->getCurrentStock()==0) {
                $product->setLocation(null);
            }
        }

        $order->setStatus(OrderStatus::DONE);
        $em->flush();

        $this->addFlash('success', "Order #{$order->getId()} approved. Stock levels updated.");
        return $this->redirectToRoute('app_manager_orders');
    }
}
