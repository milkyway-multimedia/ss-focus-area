(function($) {
	$.entwine(function($){

		$('.focus-area-field').entwine({
			onmatch: function() {
				var $this = this,
					$x1 = $this.find('.focus-area-field-point--X1'),
					$x2 = $this.find('.focus-area-field-point--X2'),
					$y1 = $this.find('.focus-area-field-point--Y1'),
					$y2 = $this.find('.focus-area-field-point--Y2'),
					width = $this.width(),
					height = $this.height(),
					select = [];

				select.push($x1.val() * width);
				select.push($y1.val() * height);
				select.push($x2.val() * width);
				select.push($y2.val() * height);

				$this.Jcrop({
					setSelect: select,
					onSelect:   function(coords) {
						$x1.val(coords.x / $this.width());
						$x2.val(coords.x2 / $this.width());
						$y1.val(coords.y / $this.height());
						$y2.val(coords.y2 / $this.height());
					},
					onRelease:   function(coords) {
						$x1.val(coords.x / $this.width());
						$x2.val(coords.x2 / $this.width());
						$y1.val(coords.y / $this.height());
						$y2.val(coords.y2 / $this.height());
					}
				});

				return this._super();
			}
		});

	});
})(jQuery);