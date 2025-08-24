document.addEventListener('DOMContentLoaded', () => {
    const chatId   = parseInt(document.getElementById('chatId').value);
    const authorId = parseInt(document.getElementById('authorId').value);
    const isMP = document.getElementById('isMP').value;

    let lastId = 0;

    function parseLayoutString(str) {
        if (!str.startsWith("[layout]")) return {layout: false, message: str};

        const result = { layout: true, action: null, params: {}, message: "" };

        // 1) Récupérer les groupes [ ... ] consécutifs depuis le début
        const re = /\[([^\]]*)\]/g;
        const groups = [];
        let m, headerEnd = 0;
        while ((m = re.exec(str)) && m.index === headerEnd) {
            groups.push(m[1]);
            headerEnd = re.lastIndex;              // fin des crochets consécutifs
        }

        // 2) Couper au PREMIER ":" après la fin des groupes (ignore les ":" internes aux params)
        let i = headerEnd;
        while (i < str.length && /\s/.test(str[i])) i++;  // ignorer espaces
        if (str[i] === ":") {
            result.message = str.slice(i + 1).trim();
        } else {
            // pas de ":", tout ce qui reste est le message (ou vide)
            result.message = str.slice(i).trim();
        }

        // 3) Extraire l'action (groupe commençant par "!")
        const actionGroup = groups.find(g => g.startsWith("!"));
        if (actionGroup) result.action = actionGroup.slice(1);

        // 4) Extraire le bloc de paramètres (premier groupe avec ":")
        const paramGroup = groups.find(g => !g.startsWith("!") && g.includes(":"));
        if (paramGroup) {
            // key:'value' | key:"value" | key:value
            const pairRe = /([A-Za-z_]\w*)\s*:\s*(?:'([^']*)'|"([^"]*)"|([^,'\]]+))/g;
            let pm;
            while ((pm = pairRe.exec(paramGroup))) {
                const key = pm[1];
                result.params[key] = (pm[2] ?? pm[3] ?? pm[4] ?? "").trim();
            }
        }

        return result;
    }

    function formatContent(content, isMP, author) {
        content = parseLayoutString(content);

        const mainDiv = document.createElement('div');
        mainDiv.classList.add('message');
        if (!isMP) {
            mainDiv.dataset.type = 'content'
        }

        const span = document.createElement('span');
        span.textContent = content.message
        mainDiv.appendChild(span);

        if (!content.layout) return mainDiv;

        if (content.action === 'confirm') {
            if (!content.params || !content.params.confirm_path || !content.params.cancel_path) return mainDiv;

            const div = document.createElement('div');
            div.classList.add('confirm-box');

            const confirmA = document.createElement('a');
            confirmA.classList.add('confirm');
            confirmA.textContent = content.params.confirm ? content.params.confirm : 'Oui'
            if (author.id === authorId) {
                confirmA.disabled = true
            } else {
                confirmA.href = content.params.confirm_path;
            }

            const cancelA = document.createElement('a');
            cancelA.classList.add('cancel');
            cancelA.textContent = content.params.cancel ? content.params.cancel : 'Non'
            if (author.id === authorId) {
                cancelA.disabled = true
            } else {
                cancelA.href = content.params.cancel_path;
            }

            div.appendChild(confirmA);
            div.appendChild(cancelA);
            mainDiv.appendChild(div);
        }

        return mainDiv;
    }

    function renderMessage(m) {
        const box = document.getElementById('chat-messages');
        const isSent = m.author.id === authorId;

        const div = document.createElement('div');
        div.classList.add('message-container', isSent ? 'sent' : 'received');
        div.dataset.id = m.author.id

        const nameSpan = document.createElement('span');
        if (!isMP) {
            nameSpan.classList.add('authorName')
            nameSpan.textContent = m.author.name
            nameSpan.dataset.type = 'name'
            div.appendChild(nameSpan);
        }

        // --- check du message précédent
        const prev = box.lastElementChild;
        if (prev && prev.dataset.id === div.dataset.id ) {
            prev.classList.add('message-group');
            if (!isMP) {
                Array.from(prev.children).forEach((child) => {
                    if (child.dataset.type === 'name') {
                        nameSpan.textContent = '';
                    }
                });
            }
        }

        div.appendChild(formatContent(m.content, isMP, m.author));
        box.appendChild(div);

        // auto-scroll en bas
        box.scrollTop = box.scrollHeight;
    }

    async function loadHistory() {
        try {
            const r = await fetch(`/messenger/chat/api/${chatId}/history?limit=50`, { cache: 'no-store' });
            const list = await r.json();
            list.forEach(renderMessage);
            if (list.length) lastId = list[list.length - 1].id;
        } catch (e) {
            console.log('Erreur fetch', e);
        } finally {
            setTimeout(pull, 1000)
        }
    }

    async function pull() {
        try {
            const r = await fetch(`/messenger/chat/api/${chatId}/pull/${lastId !== 0 ? lastId : ''}?limit=50`, { cache: 'no-store'});
            const list = await r.json();
            list.forEach(renderMessage);
            if (list.length) lastId = list[list.length - 1].id
        } catch (e) {
            console.log('Erreur fetch', e);
        } finally {
            setTimeout(pull, 1000)
        }
    }

    async function sendMessage(content) {
        if (!content.trim()) return;

        await fetch(`/messenger/chat/api/${chatId}/post`, {
            method: 'POST',
            headers: { 'Content-Type':'application/json' },
            body: JSON.stringify({ content })
        });
    }

    document.getElementById('chat-form').addEventListener('submit', (e) => {
        e.preventDefault();
        const input = document.getElementById('chat_input_field');
        sendMessage(input.value);
        input.value = '';
    });

    loadHistory();

    // Remove button
    const removeButton = document.getElementById('remove');
    if (removeButton) {
        removeButton.addEventListener('click', (e) => {
            if (!confirm('Êtes vous sûr de vouloir supprimer cet enregistrement ? Cette action est irreversible !')) {
                e.preventDefault();
            }
        });
    }

    // Masquer le loader
    const el = document.getElementById('app-loader');
    if (el) { el.classList.add('is-hidden'); setTimeout(() => el.remove(), 300); }
});
