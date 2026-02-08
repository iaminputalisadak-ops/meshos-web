<?php
/**
 * API Index
 * Lists available API endpoints
 */

header('Content-Type: application/json; charset=UTF-8');

$endpoints = [
    'products' => [
        'url' => '/api/products.php',
        'methods' => ['GET'],
        'description' => 'Get products list or single product',
        'parameters' => [
            'GET /api/products.php' => 'Get all products',
            'GET /api/products.php?category=lingerie' => 'Get products by category',
            'GET /api/products.php?id=32' => 'Get single product by ID',
            'GET /api/products.php?search=bra' => 'Search products'
        ]
    ],
    'categories' => [
        'url' => '/api/categories.php',
        'methods' => ['GET'],
        'description' => 'Get all categories'
    ],
    'cart' => [
        'url' => '/api/cart.php',
        'methods' => ['GET', 'POST', 'PUT', 'DELETE'],
        'description' => 'Cart operations',
        'parameters' => [
            'GET /api/cart.php' => 'Get cart items',
            'POST /api/cart.php' => 'Add item to cart',
            'PUT /api/cart.php' => 'Update cart item',
            'DELETE /api/cart.php?id=1' => 'Remove item from cart'
        ]
    ],
    'subscriptions' => [
        'url' => '/api/subscriptions.php',
        'methods' => ['GET', 'POST', 'PUT'],
        'description' => 'Subscription operations',
        'parameters' => [
            'GET /api/subscriptions.php?user_id=1' => 'Get user subscription',
            'POST /api/subscriptions.php' => 'Create subscription',
            'PUT /api/subscriptions.php' => 'Update subscription'
        ]
    ]
];

echo json_encode([
    'success' => true,
    'message' => 'Meesho E-commerce API',
    'version' => '1.0.0',
    'endpoints' => $endpoints
], JSON_PRETTY_PRINT);
?>


