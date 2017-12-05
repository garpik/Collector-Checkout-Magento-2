define([
	'jquery'
], function ($, collectorajax) {
	document.addEventListener('collectorCheckoutCustomerUpdated', function(event){
		console.log(event);
	});
	document.addEventListener('collectorCheckoutOrderValidationFailed', function(event){
		console.log(event);
	});
	document.addEventListener('collectorCheckoutLocked', function(event){
		console.log(event);
	});
	document.addEventListener('collectorCheckoutUnlocked', function(event){
		console.log(event);
	});
});