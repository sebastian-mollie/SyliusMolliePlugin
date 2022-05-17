<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusMolliePlugin\PHPUnit\Functional\Api;

use BitBag\SyliusMolliePlugin\Client\MollieApiClient;
use BitBag\SyliusMolliePlugin\Entity\OrderInterface;
use BitBag\SyliusMolliePlugin\Repository\OrderRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Payum\Core\Model\Identity;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\RefundPlugin\Entity\CreditMemoInterface;
use Sylius\RefundPlugin\Entity\LineItemInterface;
use Sylius\RefundPlugin\Entity\RefundInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\BitBag\SyliusMolliePlugin\PHPUnit\Functional\FunctionalTestCase;

final class RefundOrderWebhookTest extends FunctionalTestCase
{
    private MollieApiClient $mollieApiClient;

    private RepositoryInterface $securityTokenRepository;

    private OrderRepositoryInterface $orderRepository;

    private PaymentRepositoryInterface $paymentRepository;

    private RepositoryInterface $refundRepository;

    private RepositoryInterface $refundPaymentRepository;

    private RepositoryInterface $creditMemoRepository;

    private EntityManagerInterface $entityManager;

    public function setUp(): void
    {
        parent::setUp();

        $container = $this->getContainerByVersion();

        $this->mollieApiClient = $container->get('bitbag_sylius_mollie_plugin.mollie_api_client');
        $this->mollieApiClient->setApiEndpoint('http://localhost:8217');
        $this->securityTokenRepository = $container->get('sylius.repository.payment_security_token');
        $this->orderRepository = $container->get('sylius.repository.order');
        $this->paymentRepository = $container->get('sylius.repository.payment');
        $this->refundRepository = $container->get('sylius_refund.repository.refund');
        $this->refundPaymentRepository = $container->get('sylius_refund.repository.refund_payment');
        $this->creditMemoRepository = $container->get('sylius_refund.repository.credit_memo');
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
    }

    /**
     * The tested scenario:
     *
     * We simulate refunding all (to be honest only one existing) order items in order by
     * calling the notify webhook (as Mollie does).
     **/
    public function test_order_status_after_refund_with_credit_memos(): void
    {
        $fixtures = $this->loadFixturesFromFiles([
            'Api/RefundOrderWebhookTest/test_order_status_after_refund_with_credit_memos.yaml',
        ]);

        /** @var PaymentInterface $payment */
        $payment = $fixtures['order_payment'];
        $paymentId = $payment->getId();

        $notifyToken = $this->securityTokenRepository->findOneBy(['hash' => $fixtures['notify_token']->getHash()]);
        $refundToken = $this->securityTokenRepository->findOneBy(['hash' => $fixtures['refund_token']->getHash()]);
        $notifyToken->setDetails(new Identity($paymentId, Payment::class));
        $refundToken->setDetails(new Identity($paymentId, Payment::class));

        $payment = $this->paymentRepository->find($paymentId);

        $details = $payment->getDetails();
        $details['order_mollie_id'] = 'ord_' . $fixtures['order']->getId() . '-' . $fixtures['order_item']->getId();
        $payment->setDetails($details);

        $this->entityManager->flush();

        $this->request(
            Request::METHOD_POST,
            '/payment/notify/654fed',
            [],
            '',
            ['id' => 'ord_' . $fixtures['order']->getId() . '-' . $fixtures['order_item']->getId()]
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $order = $this->orderRepository->findOneByNumber('000000001');
        $this->assertEquals('refunded', $order->getPaymentState());

        $this->assertCreditMemos($order);
        $this->assertRefunds($order);
    }

    private function assertCreditMemos(OrderInterface $order): void
    {
        $memos = $this->creditMemoRepository->findByOrderId((string) $order->getId());
        $this->assertCount(1, $memos);

        /** @var CreditMemoInterface $memo */
        $memo = current($memos);
        $this->assertEquals($order->getTotal(), $memo->getTotal());
        $this->assertEquals($order->getCurrencyCode(), $memo->getCurrencyCode());

        $memoLineItems = $memo->getLineItems();
        $this->assertCount(1, $memoLineItems);

        /** @var LineItemInterface $refundLineItem */
        $refundLineItem = $memoLineItems->first();
        $this->assertEquals('Knitted wool-blend green cap', $refundLineItem->name());
        $this->assertEquals(1, $refundLineItem->quantity());
        $this->assertEquals(3832, $refundLineItem->unitNetPrice());
        $this->assertEquals(3832, $refundLineItem->unitGrossPrice());
        $this->assertEquals(3832, $refundLineItem->netValue());
        $this->assertEquals(3832, $refundLineItem->grossValue());
        $this->assertEquals(0, $refundLineItem->taxAmount());
    }

    private function assertRefunds(OrderInterface $order): void
    {
        /** @var RefundInterface $refund */
        $refund = $this->refundRepository->findOneBy(['order' => $order]);
        $this->assertNotNull($refund);

        $this->assertEquals($order->getTotal(), $refund->getAmount());

        $refundPayment = $this->refundPaymentRepository->findOneBy(['order' => $order]);
        $this->assertNotNull($refundPayment);

        $orderPayment = $order->getPayments()->first();
        $this->assertEquals($orderPayment->getAmount(), $refundPayment->getAmount());
        $this->assertEquals($orderPayment->getCurrencyCode(), $refundPayment->getCurrencyCode());
        $this->assertEquals($orderPayment->getState(), $refundPayment->getState());
    }

    private function request(
        string $method,
        string $uri,
        array $server = [],
        string $requestBody = null,
        array $parameters = [],
        array $files = []
    ): void {
        $this->client->request($method, $uri, $parameters, $files, $server, $requestBody);
    }
}
