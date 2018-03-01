<?php
namespace App\Repositories;

use App\Contracts\Repositories\CodeRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\TransactionRepositoryInterface;
use App\Libraries\PaymentStrategies\PagoefectivoPayment;
use App\Libraries\PaymentStrategies\PaymentStrategy;
use App\Libraries\PaymentStrategies\PayuPayment;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Code;

class OrderRepository implements OrderRepositoryInterface
{
    private $codeRepo;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepo;

    /**
     * @var Order
     */
    private $orderModel;

    /**
     * @var PaymentStrategy
     */
    private $paymentStrategy;

    /**
     * Array containing all extra parameters the might be required
     *
     * @var array
     */
    private $extraParameters;

    public function __construct(
        CodeRepositoryInterface $codeRepo,
        TransactionRepositoryInterface $transactionRepo,
        Order $orderModel
    ) {
        $this->codeRepo        = $codeRepo;
        $this->transactionRepo = $transactionRepo;
        $this->orderModel      = $orderModel;
    }

    public function generateOrder(
        User $user,
        array $products,
        array $combos,
        $paymentMethod,
        $source,
        Code $discountCode = null
    ) {
        $order = $this->createOrder($user, $products, $combos, $paymentMethod, $source, $discountCode);

        if ($order->code_amount > 0) {
            $this->transactionRepo->createCodeTransaction($order);
        }

        if ($order->getMoneyAmount() > 0) {
            switch ($order->payment_method) {
                case Order::PAYMENT_METHOD_DEPOSITO:
                    $transaction = $this->transactionRepo->createDepositTransaction($order);
                    $this->paymentStrategy = new PagoefectivoPayment();
                    break;
                case Order::PAYMENT_METHOD_CARD:
                    $transaction = $this->transactionRepo->createTransaction(
                        $order,
                        Order::PAYMENT_METHOD_CARD,
                        $order->money_amount
                    );
                    $this->paymentStrategy = new PayuPayment();
                    $this->paymentStrategy->setCardData($this->extraParameters);
                    break;
            }

            /* @var $paymentResponse \App\Libraries\PaymentStrategies\PaymentResponse */
            $paymentResponse = $this->paymentStrategy->pay($order, $transaction, $user);
            // TODO verify first if payment is not deferred (PagoEfectivo)
            // when it is implemented
            if ($paymentResponse->transactionIsApproved()) {
                $order->status = Order::STATUS_NUEVO;
                $transaction->status = Transaction::STATUS_EXITOSO;
            } else {
                $order->status = Order::STATUS_CAPTURADO;
                $transaction->status = Transaction::STATUS_FALLIDO;
            }

            $order->vendor_response_message = $paymentResponse->getResponseMessage();

            $transaction->payment_vendor = $this->paymentStrategy->getVendor();
            $transaction->vendor_transaction_id = $paymentResponse->getTransactionId();
            $transaction->vendor_order_id = $paymentResponse->getOrderId();
            $transaction->vendor_response_code = $paymentResponse->getResponseCode();

            $order->save();
            $transaction->save();

        }

        return $order;

    }

    public function createOrder(User $user, array $products, array $combos,
        $paymentMethod, $source, Code $discountCode = null
    ) {
        $orderAmount = $this->getItemsAmount(array_merge($products, $combos));
        $discountAmount = 0;

        if (! is_null($discountCode)) {
            $discountAmount = $this->codeRepo->getDiscountCodeAmmount(
                $discountCode,
                $orderAmount
            );
        }

        $moneyAmount = $orderAmount - $discountAmount;

        $order                 = new Order();
        $order->money_amount   = $moneyAmount;
        $order->code_amount    = $discountAmount;
        $order->source         = $source;
        $order->payment_method = $paymentMethod;
        $order->status         = Order::STATUS_INICIAL;
        $order->user_id        = $user->id;
        if (! is_null($discountCode)) {
            $order->code_id = $discountCode->id;
        }
        $order->save();

        $productsArrayToAttach = [];
        foreach ($products as $pair) {
            $product  = $pair['item'];
            $quantity = $pair['quantity'];

            $productsArrayToAttach[(int) $product->id] = [
                'quantity' => $quantity
            ];
        }
        $order->products()->attach($productsArrayToAttach);

        $combosArrayToAttach = [];
        foreach ($combos as $pair) {
            $combo  = $pair['item'];
            $quantity = $pair['quantity'];

            $combosArrayToAttach[(int) $combo->id] = [
                'quantity' => $quantity
            ];
        }
        $order->combos()->attach($combosArrayToAttach);

        return $order;

    }

    public function getItemsAmount(array $items = []) {
        $amount = 0;

        foreach ($items as $pair) {
            $item     = $pair['item']; //product or combo
            $quantity = $pair['quantity'];

            $amount += $item->getAmount($quantity);
        }

        return $amount;
    }

    public function updateStatus(Order &$order, $status)
    {
        $order->status = $status;
        $order->save();

        return $order;
    }

    public function getOrdersByUser(User $user, $type)
    {
        if ($type == Order::TYPE_PURCHASE) {
            $orders = $this->orderModel
                ->where('user_id', $user->id)
                ->whereIn('status', $this->orderModel->getPurchaseStatuses())
                ->with('products')
                ->get();

        } elseif ($type == Order::TYPE_ORDER) {
            $orders = $this->orderModel
                ->where('user_id', $user->id)
                ->whereIn('status', $this->orderModel->getOrderStatuses())
                ->with('products')
                ->get();
        }

        return $orders;
    }

    public function setExtraParameters($params)
    {
        $this->extraParameters = $params;
    }
}