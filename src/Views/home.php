<?php
$config = include __DIR__ . '/../config/options.php';
$expirations = $config['expiration'];
$exposures = $config['exposure'];

$pageTitle = 'Text Snippet Sharing Service';
require __DIR__ . '/layout/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Left Column: Editor and Settings -->
        <div class="col-lg-9">
            <div id="error-container"></div>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">New Paste</h5>
                </div>
                <div class="card-body">
                    <!-- Monaco Editor Container -->
                    <div id="editor-container" style="height: 400px; border: 1px solid #ccc;"></div>

                    <!-- Paste Settings -->
                    <div class="mt-3">
                        <h5 class="mb-3">Paste Settings</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" placeholder="Enter paste title (optional)">
                            </div>
                            <div class="col-md-6">
                                <label for="language-select" class="form-label">Syntax Highlighting</label>
                                <select id="language-select" class="form-select"></select>
                            </div>
                            <div class="col-md-6">
                                <label for="expiration-select" class="form-label">Paste Expiration</label>
                                <select id="expiration-select" class="form-select">
                                    <?php foreach ($expirations as $label => $value): ?>
                                        <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="exposure-select" class="form-label">Paste Exposure</label>
                                <select id="exposure-select" class="form-select">
                                    <?php foreach ($exposures as $label => $value): ?>
                                        <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4 mb-2 text-center">
                            <button type="button" id="create-paste" class="btn btn-primary">Create New Paste</button>
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
    class Editor
    {
        constructor(containerId) {
            this.containerId = containerId;
            this.editor = null;
        }

        initialize() {
            require.config({
                paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs' }
            });

            require(['vs/editor/editor.main'], () => {
                this._createEditor();
                this._setupLanguageHandler();
                this._registerPasteEvent();
            });
        }

        _createEditor() {
            this.editor = monaco.editor.create(document.getElementById(this.containerId), {
                value: '',
                language: 'plaintext',
                theme: 'vs-dark',
                minimap: { enabled: false },
                automaticLayout: true,
                padding: {
                    top: 8
                }
            });
        }

        _setupLanguageHandler() {
            const languageSelect = document.getElementById('language-select');
            const languages = monaco.languages.getLanguages();

            languages.sort((a, b) => a.id.localeCompare(b.id));

            const plainTextOption = new Option('None', 'plaintext');
            languageSelect.append(plainTextOption);

            languages.forEach(lang => {
                if (lang.id !== 'plaintext') {
                    const displayName = lang.aliases?.[0] || lang.id;
                    const option = new Option(displayName, lang.id);
                    languageSelect.append(option);
                }
            });

            languageSelect.addEventListener('change', e => {
                monaco.editor.setModelLanguage(this.editor.getModel(), e.target.value);
            });
        }

        _registerPasteEvent() {
            document.getElementById('create-paste').addEventListener('click', async () => {
                try {
                    const input = {
                        content: this.editor.getValue(),
                        title: document.getElementById('title').value || 'Untitled',
                        language: document.getElementById('language-select').value,
                        expiresAt: document.getElementById('expiration-select').value,
                        exposure: document.getElementById('exposure-select').value
                    };

                    this._validateInput(input);

                    const response = await fetch('/api/paste', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(input)
                    });

                    if (!response.ok) {
                        const errorResponse = await response.json().catch(() => ({
                            error: response.status === 503
                                ? 'Too many requests. Please wait a moment before trying again.'
                                : 'An unknown error occurred. Please contact the admin.'
                        }));
                        throw new Error(errorResponse.error);
                    }
                    const data = await response.json();
                    window.location.href = `/${data.hash_id}`;
                } catch (error) {
                    this._showError(error.message);
                }
            });
        }

        _validateInput(input) {
            if (!input.content) {
                throw new Error('You cannot create an empty paste.');
            }
            if (input.title.length > 255) {
                throw new Error('Title is too long (maximum is 255 characters)');
            }

            const inputSize = new Blob([JSON.stringify(input)]).size;
            const maxSize = 8388608; // 8 MB

            if (inputSize > maxSize) {
                const formatter = new Intl.NumberFormat('en-US', {
                    style: 'unit',
                    unit: 'megabyte',
                    unitDisplay: 'short',
                    maximumFractionDigits: 2
                });

                throw new Error(
                    `Content size ${formatter.format(inputSize / (1024 * 1024))} exceeds the maximum allowed size ${formatter.format(maxSize / (1024 * 1024))}`
                );
            }
        }

        _showError(message) {
            const errorContainer = document.getElementById('error-container');
            errorContainer.innerHTML = `
            <div class="alert alert-danger d-flex align-items-center border-danger mb-3" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16">
                    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                </svg>
                <div>${message}</div>
            </div>`;
        }
    }
    const editor = new Editor('editor-container');
    editor.initialize();
</script>
</body>
</html>