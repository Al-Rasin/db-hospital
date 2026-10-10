    </main>

  </div>
</div>

<script src="<?php echo BASE; ?>/assets/vendor/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('submit', function (e) {
    var btn = e.target.querySelector('[type=submit]');
    if (btn) {
        btn.disabled = true;
        btn.style.opacity = '0.65';
    }
});
</script>
</body>
</html>
