
document.addEventListener('DOMContentLoaded', function() {
	const btn = document.querySelector('.welcome-btn');
	if (btn) {
		btn.addEventListener('mousemove', function(e) {
			btn.style.transform = 'scale(1.08)';
		});
		btn.addEventListener('mouseleave', function(e) {
			btn.style.transform = '';
		});
	}
});
