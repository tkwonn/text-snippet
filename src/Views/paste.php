<?php
/**
 * @var array $paste
 */
$pageTitle = $paste['title'] ?? 'Untitled Paste';
require __DIR__ . '/layout/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Left Column: Editor -->
        <div class="col-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><?= htmlspecialchars($paste['title'] ?? 'Untitled Paste') ?></h5>
                    <div class="text-muted small">
                        Created: <?= htmlspecialchars($paste['created_at']) ?>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Monaco Editor Container (Read-only) -->
                    <div id="editor-container" style="height: 600px; border: 1px solid #ccc;"></div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-end gap-3">
                        <div class="text-muted small">
                            Language: <?= htmlspecialchars($paste['language']) ?>
                        </div>
                        <div class="text-muted small">
                            Expires: <?= htmlspecialchars($paste['expires_at']) ?>
                        </div>
                        <div class="text-muted small">
                            Exposure: <?= htmlspecialchars($paste['exposure']) ?>
                        </div>
                        <div class="text-muted small">
                            Size: <?= htmlspecialchars($paste['size']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Public Pastes -->
        <div class="col-lg-3">
            <?php include __DIR__ . '/components/public_pastes.php'; ?>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs/loader.js"></script>
<script>
    const pasteData = <?= json_encode($paste) ?>;

    class ViewEditor {
        constructor(containerId, content, language) {
            this.containerId = containerId;
            this.content = content;
            this.language = language;
            this.editor = null;
        }

        initialize() {
            require.config({
                paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs' }
            });

            require(['vs/editor/editor.main'], () => {
                this._createEditor();
            });
        }

        _createEditor() {
            this.editor = monaco.editor.create(document.getElementById(this.containerId), {
                value: this.content,
                language: this.language,
                theme: 'vs-dark',
                readOnly: true,
                minimap: { enabled: false },
                automaticLayout: true,
                padding: { top: 8 }
            });
        }
    }

    const editor = new ViewEditor(
        'editor-container',
        pasteData.content,
        pasteData.language
    );
    editor.initialize();
</script>
</body>
</html>