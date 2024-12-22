<?php
$pageTitle = 'Paste Expired';
require __DIR__ . '/layout/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Left Column: Expired Message -->
        <div class="col-9">
            <div class="card">
                <div class="card-body text-center py-5">
                    <h3 class="text-muted mb-4">This paste has expired</h3>
                    <p class="text-muted mb-4">The paste you're trying to access is no longer available.</p>
                </div>
            </div>
        </div>

        <!-- Right Column: Public Pastes -->
        <div class="col-lg-3">
            <?php include __DIR__ . '/components/public_pastes.php'; ?>
        </div>
    </div>
</div>
</body>
</html>