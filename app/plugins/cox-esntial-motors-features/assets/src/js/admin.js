/**
 * Write your JS code here for admin.
 */
const copyToClipboard = document.querySelectorAll('.copyToClipboard');
copyToClipboard.forEach((button) => {
	button.addEventListener('click', (e) => {
		e.preventDefault();
		const input = button.previousElementSibling;
		input.select();
		document.execCommand('copy');
	});
});
