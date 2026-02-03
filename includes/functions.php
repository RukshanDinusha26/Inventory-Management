<?php
function validateInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function validateOrderForm($data) {
    $errors = [];

    if (empty($data['customer_id'])) {
        $errors['customer_id'] = 'Customer ID is required.';
    }

    if (empty($data['products']) || !is_array($data['products'])) {
        $errors['products'] = 'At least one product is required.';
    } else {
        foreach ($data['products'] as $index => $product) {
            if (empty($product['product_id'])) {
                $errors["products[$index][product_id]"] = 'Product ID is required.';
            }
            if (empty($product['quantity']) || !is_numeric($product['quantity'])) {
                $errors["products[$index][quantity]"] = 'Quantity is required and must be a number.';
            }
            if (empty($product['price']) || !is_numeric($product['price'])) {
                $errors["products[$index][price]"] = 'Price is required and must be a number.';
            }
        }
    }

    return $errors;
}
?>