<?php
// Simple header partial: inject shared stylesheet and render flash messages (toasts)
if (session_status() === PHP_SESSION_NONE) {
	@session_start();
}
?>
<link rel="stylesheet" href="/OOP/SupportSystem/public/css/app.css">

<?php if(!empty($_SESSION['flash'])): ?>
	<div class="toast-container" aria-live="polite">
		<?php foreach((array)$_SESSION['flash'] as $flash): ?>
			<?php $type = ($flash['type'] ?? 'success'); $msg = ($flash['message'] ?? ''); ?>
			<div class="toast toast-<?php echo $type === 'error' ? 'error' : 'success'; ?> toast-show" role="status">
				<span><?php echo htmlspecialchars($msg); ?></span>
				<button class="toast-close" onclick="this.parentElement.style.display='none'">✕</button>
			</div>
		<?php endforeach; ?>
	</div>
	<script>
		(function(){
			// auto dismiss after 4s
			setTimeout(function(){
				var toasts = document.querySelectorAll('.toast');
				toasts.forEach(function(t){ t.classList.remove('toast-show'); t.style.display='none'; });
			}, 4000);
		})();
	</script>
<?php
	// clear flash after rendering
	unset($_SESSION['flash']);
endif;
?>
