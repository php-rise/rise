<?php
/**
 * Routes.
 */

/**
 * @var \Rise\Router\Scope $scope
 */

$scope->createScope(function($scope) {
	$scope->get('', 'Home.index', 'root');
	$scope->get('contact', 'Contact.index', 'contact');
	$scope->get('products/{id}', 'Product.show', 'productDetail');
});
