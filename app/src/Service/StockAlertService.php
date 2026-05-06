<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class StockAlertService
{
    public function __construct(
        private UserRepository  $userRepository,
        private MailerInterface $mailer
    )
    {
    }

    /**
     * Checks a list of modified products and sends ONE email if any are low on stock.
     *
     * @param Product[] $productsToCheck
     */
    public function checkAndSendAlert(array $productsToCheck): void
    {
        $lowStockProducts = [];

        foreach ($productsToCheck as $product) {
            if ($product->getCurrentStock() <= $product->getLowStockThreshold()) {
                $lowStockProducts[] = $product;
            }
        }

        if (empty($lowStockProducts)) {
            return;
        }

        $managers = $this->userRepository->findManagers();
        if (empty($managers)) {
            return;
        }

        $email = (new TemplatedEmail())
            ->from('warehouse@demomailtrap.co')
            ->subject('Action Required: Low Stock Alert')
            ->htmlTemplate('email/low_stock_alert.html.twig')
            ->context([
                'products' => $lowStockProducts,
            ]);

        foreach ($managers as $manager) {
            $email->addBcc($manager->getEmail());
        }

        $this->mailer->send($email);
    }
}
