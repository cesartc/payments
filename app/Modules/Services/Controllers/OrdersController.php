<?php
namespace App\Modules\Services\Controllers;

use App\Contracts\Repositories\AsdRepositoryInterface;
use App\Contracts\Repositories\CodeRepositoryInterface;
use App\Contracts\Repositories\ComboRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Helpers\Response;
use App\Models\Code;
use App\Models\Combo;
use App\Models\Order;
use App\Models\Product;
use App\Transformers\ComboTransformer;
use App\Transformers\OrderTransformer;
use App\Transformers\ProductTransformer;
use App\Validators\OrderValidator;
use App\Validators\PaymentCardValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrdersController extends ServicesController
{
    private $orderRepo;
    private $productRepo;
    private $comboRepo;
    private $codeRepo;

    private $productTransformer;
    private $comboTransformer;

    private $orderValidator;
    private $paymentCardValidator;

    public function __construct(
        OrderRepositoryInterface $orderRepo,
        ProductRepositoryInterface $productRepo,
        ComboRepositoryInterface $comboRepo,
        CodeRepositoryInterface $codeRepo,
        ProductTransformer $productTransformer,
        ComboTransformer $comboTransformer,
        OrderValidator $orderValidator,
        PaymentCardValidator $paymentCardValidator
    ) {
        $this->orderRepo   = $orderRepo;
        $this->productRepo = $productRepo;
        $this->comboRepo   = $comboRepo;
        $this->codeRepo    = $codeRepo;

        $this->productTransformer = $productTransformer;
        $this->comboTransformer   = $comboTransformer;

        $this->orderValidator = $orderValidator;
        $this->paymentCardValidator = $paymentCardValidator;
    }

    public function create(Request $request)
    {
        $params    = $request->all();
        $validator = $this->orderValidator->getOrderValidator($params);
        $this->executeValidation($validator);

        if ($params['payment_method'] == Order::PAYMENT_METHOD_CARD) {
            $validator = $this->paymentCardValidator->getPaymentCardValidator($params);
            $this->executeValidation($validator);
        }

        if (! array_key_exists('products', $params)) {
            $params['products'] = [];
        }
        if (! array_key_exists('combos', $params)) {
            $params['combos'] = [];
        }

        Order::validateItemsRequest(
            array_merge($params['products'], $params['combos'])
        );

        $paymentMethod = $params['payment_method'];
        $user          = Auth::user();
        $source        = Order::SOURCE_MOVIL;
        $code          = null;

        $user->setIp($request->ip());
        $user->setAgent($request->server('HTTP_USER_AGENT'));

        if (! empty($params['code'])) {
            $code = $this->codeRepo->getValidatedDiscountCode(
                $params['code'],
                Code::SOURCE_MOVIL,
                $user
            );
        }

        $productIds = array_column($params['products'], 'id');
        $products   = $this->productRepo->allIn($productIds);
        if (count($params['products']) != count($products)) {
            abort(400, 'Some of the products were not found');
        }

        $comboIds = array_column($params['combos'], 'id');
        $combos   = $this->comboRepo->allIn($comboIds);
        if (count($params['combos']) != count($combos)) {
            abort(400, 'Some of the combos were not found');
        }

        $formatedProducts = Order::formatItems($params['products'], $products);
        $formatedCombos   = Order::formatItems($params['combos'], $combos);

        $this->orderRepo->setExtraParameters($params);

        $order = $this->orderRepo->generateOrder($user, $formatedProducts,
            $formatedCombos, $paymentMethod, $source, $code);


        return Response::json(
            $order->isSuccessful(),
            $order->vendor_response_message,
            [
                'pedido_id' => $order->getId(),
                'money_amount' => $order->money_amount,
                'cip' => (string) rand(10000000, 99999999),
                'expiration_date' => rand(1, 30) . ' - OCT - 2017',
                'products' => $this->productTransformer
                    ->transformForGeneratedOrder($formatedProducts),
                'combos' => $this->comboTransformer
                    ->transformForGeneratedOrder($formatedCombos)
            ]
        );
    }


}