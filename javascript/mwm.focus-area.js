(function($) {
	$.entwine(function($){

		$('.focusarea .focusarea').entwine({
			onmatch: function() {
				var $this = this,
					$x1 = $this.find('.focusarea-point--X1'),
					$x2 = $this.find('.focusarea-point--X2'),
					$y1 = $this.find('.focusarea-point--Y1'),
					$y2 = $this.find('.focusarea-point--Y2'),
					$width = $this.find('.focusarea-point--Width'),
					$height = $this.find('.focusarea-point--Height'),
					$top = $this.find('.focusarea-point--FromTop'),
					$left = $this.find('.focusarea-point--FromLeft'),
					$right = $this.find('.focusarea-point--FromRight'),
					$bottom = $this.find('.focusarea-point--FromBottom'),
					select = [$x1.val(), $y1.val(), $x2.val(), $y2.val()],
					fn = function(coords) {
						$x1.val(coords.x);
						$y1.val(coords.y);
						$x2.val(coords.x2);
						$y2.val(coords.y2);
						$width.val(coords.w);
						$height.val(coords.h);

						$top.val(coords.y / $this.height());
						$left.val(coords.x / $this.width());
						$right.val(1 - (coords.x2 / $this.width()));
						$bottom.val(1 - (coords.y2 / $this.height()));
					};

				$this.Jcrop({
					setSelect: select,
					onSelect:   fn,
					onRelease:   fn
				});

				return this._super();
			}
		});

	});
})(jQuery);