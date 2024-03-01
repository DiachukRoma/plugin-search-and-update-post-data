jQuery(document).ready(function ($) {
	$('#search-form').on('submit', function (e) {
		e.preventDefault();
		onloadTable();
	});

	function onloadTable() {
		$.ajax({
			url: admin_ajax.url,
			type: 'POST',
			data: {
				action: 'search_posts',
				slug: $('.posts__text').val()
			},
			success: function (data) {
				$('.table-result').html(data);
				updateTable();
			}
		});
	}

	function updateTable() {
		$('.form-replace').on('submit', function (e) {
			e.preventDefault();
			const item = $('input', this);
			let idx = [];
			$(`.${item.attr('name')}`).each((index, obj) => {
				idx.push($(obj).parent().attr('data-id'));
			})

			if (item.val().length) {
				$.ajax({
					url: admin_ajax.url,
					type: 'POST',
					data: {
						action: 'set_new_data',
						field: item.attr('name'),
						value: item.val(),
						idx,
					},
					success: function () {
						onloadTable();
					}
				});
			}
		});
	}
});