<?php if ($currentPage !== 'login.php'): ?>
        </main>
    </div>
<?php endif; ?>

<script>
    function showSuccess(msg) {
        Swal.fire({ icon: 'success', title: 'Success', text: msg, background: '#061121', color: '#fff' });
    }
    
    function showError(msg) {
        Swal.fire({ icon: 'error', title: 'Error', text: msg, background: '#061121', color: '#fff' });
    }
</script>
</body>
</html>
