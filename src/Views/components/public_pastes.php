<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Public Pastes</h5>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush" id="publicPastes"></div>
    </div>
</div>

<script>
    async function loadPublicPastes() {
        try {
            const response = await fetch('/api/pastes', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            });
            if (!response.ok) {
                throw new Error(`Failed to fetch public pastes: ${response.status} ${response.statusText}`);
            }

            const data = await response.json();
            if (!Array.isArray(data)) {
                throw new Error('Invalid API response format.');
            }

            const container = document.getElementById('publicPastes');

            if (data.length === 0) {
                container.innerHTML = `
                    <div class="list-group-item">
                        No public pastes were found.
                    </div>
                `;
                return;
            }

            container.innerHTML = data.map(paste => `
                <a href="/${paste.hash}" class="list-group-item list-group-item-action py-2">
                    <div class="small text-primary mb-1">${paste.title}</div>
                    <div class="d-flex gap-2 align-items-center">
                        <small class="text-muted">${paste.language}</small>
                        <small class="text-muted">|</small>
                        <small class="text-muted">${paste.created_at}</small>
                        <small class="text-muted">|</small>
                        <small class="text-muted">${paste.size}</small>
                    </div>
                </a>
            `).join('');
        } catch (error) {
            console.error(error);
            document.getElementById('publicPastes').innerHTML = `
                <div class="list-group-item text-danger">
                    Failed to load public pastes.
                </div>
            `;
        }
    }

    loadPublicPastes();
</script>
