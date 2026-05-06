<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\ContractStatus;
use App\Enum\Location;
use App\Repository\ContractRepository;
use App\Service\ContractService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ContractController extends AbstractController
{
    #[Route('/contract/buy/{supplierId}/{productId}', name: 'app_contract_buy', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function buyProduct(
        int $supplierId,
        int $productId,
        Request $request,
        ContractService $contractService
    ): Response {
        /** @var User $manager */
        $manager = $this->getUser();

        $quantity = $request->request->getInt('quantity', 1);

        $rampValue = $request->request->getString('delivery_ramp');

        $ramp = Location::tryFrom($rampValue);

        if (!$ramp) {
            $this->addFlash('error', 'Please select a valid delivery ramp.');
            return $this->redirectToRoute('app_home');
        }

        $result = $contractService->createContract($manager, $supplierId, $productId, $quantity, $ramp);

        if (!$result['success']) {
            $this->addFlash('error', $result['message']);
        } else {
            $this->addFlash('success', $result['message']);
        }

        return $this->redirectToRoute('app_stock_index');
    }

    #[Route('/contracts', name: 'app_contract_index', methods: ['GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_MANAGER") or is_granted("ROLE_SUPPLIER")'))]
    public function index(ContractRepository $contractRepo): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (in_array('ROLE_SUPPLIER', $user->getRoles())) {
            $contracts = $contractRepo->findBy(['supplier' => $user->getSupplierProfile()]);
        }
        else {
            $contracts = $contractRepo->findBy(['manager' => $user]);
        }

        return $this->render('contract/index.html.twig', [
            'contracts' => $contracts,
        ]);
    }

    #[Route('/contract/{id}/accept', name: 'app_contract_accept', methods: ['POST'])]
    #[IsGranted('ROLE_SUPPLIER')]
    public function acceptContract(int $id, ContractService $contractService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $result = $contractService->updateContractStatus($id, $user, ContractStatus::DONE);

        if (!$result['success']) {
            $this->addFlash('error', $result['message']);
        } else {
            $this->addFlash('success', $result['message']);
        }

        return $this->redirectToRoute('app_contract_index');
    }

    #[Route('/contract/{id}/reject', name: 'app_contract_reject', methods: ['POST'])]
    #[IsGranted('ROLE_SUPPLIER')]
    public function rejectContract(int $id, ContractService $contractService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $result = $contractService->updateContractStatus($id, $user, ContractStatus::REJECTED);

        if (!$result['success']) {
            $this->addFlash('error', $result['message']);
        } else {
            $this->addFlash('success', $result['message']);
        }

        return $this->redirectToRoute('app_contract_index');
    }
}
