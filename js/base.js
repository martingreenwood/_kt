var $ = jQuery;


(function($) {
	$.fn.rotator = function(options) {
		options = $.extend({
			blocks: '.image',
			speed: 1000,
			delay: 8000
		}, options);
		var setZIndex = function(element) {
				var index = $(options.blocks, element).length;
				$(options.blocks, element).each(function() {
					index--;
					$(this).css('zIndex', index);
				});
			};
		var rotate = function(element) {
				var blocks = $(options.blocks, element),
					len = blocks.length,
					index = -1;
				blocks.fadeIn(options.speed);
				var timer = setInterval(function() {
					index++;
					var block = blocks.eq(index);
					if (index == len) {
						clearInterval(timer);
						rotate(element);
					}
					if (block.index() != (len - 1)) {
						block.fadeOut(options.speed);
					}
				}, options.delay);
			};
		return this.each(function() {
			var elem = $(this);
			setZIndex(elem);
			rotate(elem);
		});
	};
})(jQuery);


$(function () {
	$('.slide').rotator({
		speed: 2500,
		delay: 7000
	});
});