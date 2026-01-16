class CounterPanel {
    constructor(element) {
        this.element = element;
        this.routes = JSON.parse(element.dataset.routes);
        this.elements = {
            headerTime: document.getElementById('headerTime'),
            headerDate: document.getElementById('headerDate'),
            currentNumber: document.getElementById('currentNumber'),
            dockCurrentNumber: document.getElementById('dockCurrentNumber'),
            waitingList: document.getElementById('waitingList'),
            skippedList: document.getElementById('skippedList'),
            btnNotify: document.getElementById('btnNotify'),
            btnSkip: document.getElementById('btnSkip'),
            btnComplete: document.getElementById('btnComplete'),
            btnTransfer: document.getElementById('btnTransfer'),
            btnCallNext: document.getElementById('btnCallNext'),
            skipModal: document.getElementById('skip-modal'),
            skipModalContent: document.getElementById('skip-modal-content'),
            transferModal: document.getElementById('transfer-modal'),
            transferModalContent: document.getElementById('transfer-modal-content'),
            countersList: document.getElementById('countersList'),
            btnToggleMinimize: document.getElementById('btnToggleMinimize'),
            panelHeader: document.getElementById('panelHeader'),
            panelMain: document.getElementById('panelMain'),
            panelDock: document.getElementById('panelDock'),
        };

        this.state = {
            currentQueue: null,
            onlineCounters: [],
            isMinimized: false,
            fetchInFlight: false,
            fetchController: null,
            buttonCooldowns: new Map(),
            selectedTransferQueueId: null,
        };

        this.ACTION_COOLDOWN_SECONDS = 3;

        this.init();
    }

    init() {
        this.updateHeaderTime();
        setInterval(() => this.updateHeaderTime(), 1000);

        this.loadMinimizedState();
        this.attachEventListeners();

        this.fetchData();
        setInterval(() => this.fetchData(), 1000);
    }

    attachEventListeners() {
        this.elements.btnNotify?.addEventListener('click', (e) => this.handleNotify(e));
        this.elements.btnSkip?.addEventListener('click', () => this.openSkipModal());
        this.elements.btnComplete?.addEventListener('click', (e) => this.handleComplete(e));
        this.elements.btnCallNext?.addEventListener('click', (e) => this.handleCallNext(e));
        this.elements.btnTransfer?.addEventListener('click', () => this.openTransferModal());
        this.elements.btnToggleMinimize?.addEventListener('click', () => this.toggleMinimize());

        document.getElementById('btnConfirmSkip')?.addEventListener('click', (e) => this.handleSkip(e));
        document.getElementById('btnCloseSkipModal')?.addEventListener('click', () => this.closeSkipModal());
        document.getElementById('btnCloseTransferModal')?.addEventListener('click', () => this.closeTransferModal());
    }

    updateHeaderTime() {
        const now = new Date();
        this.elements.headerTime.textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
        this.elements.headerDate.textContent = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    }

    fetchData() {
        if (this.state.fetchInFlight) return;
        this.state.fetchInFlight = true;

        if (this.state.fetchController) {
            this.state.fetchController.abort();
        }
        this.state.fetchController = new AbortController();

        fetch(this.routes.data, {
            cache: 'no-store',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
            signal: this.state.fetchController.signal,
        })
        .then(r => r.json())
        .then(d => { if (d.success) this.render(d); })
        .catch(err => {
            if (err.name !== 'AbortError') {
                console.error('Counter refresh failed:', err);
            }
        })
        .finally(() => {
            this.state.fetchInFlight = false;
        });
    }

    render(data) {
        this.state.currentQueue = data.current_queue;
        this.state.onlineCounters = data.online_counters || [];

        const current = this.state.currentQueue ? this.formatDisplayQueue(this.state.currentQueue.queue_number) : '---';
        this.elements.currentNumber.textContent = current;
        if (this.elements.dockCurrentNumber) this.elements.dockCurrentNumber.textContent = current;

        this.updateActionButtons();
        this.renderWaitingList(data.waiting_queues);
        this.renderSkippedList(data.skipped);
    }

    updateActionButtons() {
        const hasCurrentQueue = !!this.state.currentQueue;
        const hasWaitingQueues = this.elements.waitingList.children.length > 0;

        this.setButtonState(this.elements.btnNotify, hasCurrentQueue);
        this.setButtonState(this.elements.btnSkip, hasCurrentQueue);
        this.setButtonState(this.elements.btnComplete, hasCurrentQueue);
        this.setButtonState(this.elements.btnTransfer, hasCurrentQueue && this.state.onlineCounters.length > 0);
        this.setButtonState(this.elements.btnCallNext, hasWaitingQueues && !hasCurrentQueue);
    }

    setButtonState(btn, enabled) {
        if (!btn) return;
        btn.disabled = !enabled || this.isButtonCooling(btn);
    }

    renderWaitingList(waitingQueues) {
        this.elements.waitingList.innerHTML = '';
        if (waitingQueues && Array.isArray(waitingQueues)) {
            waitingQueues.forEach(w => {
                const row = document.createElement('div');
                row.className = 'p-3 border rounded flex justify-between items-center';
                row.innerHTML = `<span class="font-semibold">${this.formatDisplayQueue(w.queue_number)}</span>`;
                this.elements.waitingList.appendChild(row);
            });
        }
    }

    renderSkippedList(skipped) {
        this.elements.skippedList.innerHTML = '';
        skipped.forEach(s => {
            const row = document.createElement('div');
            row.className = 'p-3 border rounded flex justify-between items-center bg-orange-50';
            row.innerHTML = `<span class="font-semibold text-orange-700">${this.formatDisplayQueue(s.queue_number)}</span>
                             <button type="button" class="bg-blue-600 text-white px-3 py-1 rounded">Recall</button>`;
            row.querySelector('button').addEventListener('click', () => this.handleRecall(s.id));
            this.elements.skippedList.appendChild(row);
        });
    }

    formatDisplayQueue(queueNumber) {
        if (!queueNumber) return '---';
        const parts = String(queueNumber).split('-');
        return parts.length ? (parts[parts.length - 1] || String(queueNumber)) : String(queueNumber);
    }

    playNotificationSound() {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const now = audioContext.currentTime;
        const playChime = (startTime, frequency, duration) => {
            const osc = audioContext.createOscillator();
            const gain = audioContext.createGain();
            osc.connect(gain);
            gain.connect(audioContext.destination);
            osc.frequency.value = frequency;
            osc.type = 'sine';
            gain.gain.setValueAtTime(0.35, startTime);
            gain.gain.exponentialRampToValueAtTime(0.01, startTime + duration);
            osc.start(startTime);
            osc.stop(startTime + duration);
        };
        playChime(now, 523, 0.35);
        playChime(now + 0.4, 659, 0.35);
        playChime(now + 0.8, 784, 0.4);
    }

    runActionWithCooldown(btnEl, actionFn, seconds = this.ACTION_COOLDOWN_SECONDS) {
        if (btnEl && this.isButtonCooling(btnEl)) return;
        if (btnEl) this.startButtonCooldown(btnEl, seconds);

        return Promise.resolve()
            .then(actionFn)
            .catch(err => {
                if (btnEl && btnEl.id) {
                    this.state.buttonCooldowns.delete(btnEl.id);
                    if (btnEl.dataset.originalHtml) btnEl.innerHTML = btnEl.dataset.originalHtml;
                    delete btnEl.dataset.originalHtml;
                }
                console.error(err);
                alert(err?.message || 'Action failed. Please try again.');
            });
    }

    isButtonCooling(btnEl) {
        return this.state.buttonCooldowns.has(btnEl.id) && this.state.buttonCooldowns.get(btnEl.id) > Date.now();
    }

    getCooldownRemainingSeconds(btnEl) {
        if (!this.isButtonCooling(btnEl)) return 0;
        const remainingMs = this.state.buttonCooldowns.get(btnEl.id) - Date.now();
        return Math.ceil(remainingMs / 1000);
    }

    startButtonCooldown(btnEl, seconds) {
        this.state.buttonCooldowns.set(btnEl.id, Date.now() + seconds * 1000);
        if (!btnEl.dataset.originalHtml) {
            btnEl.dataset.originalHtml = btnEl.innerHTML;
        }
        btnEl.disabled = true;

        const tick = () => {
            const remaining = this.getCooldownRemainingSeconds(btnEl);
            if (remaining <= 0) {
                this.state.buttonCooldowns.delete(btnEl.id);
                if (btnEl.dataset.originalHtml) btnEl.innerHTML = btnEl.dataset.originalHtml;
                delete btnEl.dataset.originalHtml;
                this.updateActionButtons();
                return;
            }
            const baseHtml = btnEl.dataset.originalHtml || btnEl.innerHTML;
            btnEl.innerHTML = `${baseHtml} <span class="ml-2 text-xs opacity-90">(${remaining}s)</span>`;
        };

        tick();
        const timer = setInterval(() => {
            const remaining = this.getCooldownRemainingSeconds(btnEl);
            if (remaining <= 0) {
                clearInterval(timer);
                tick();
                return;
            }
            tick();
        }, 250);
    }

    getJson(url) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value;
        const headers = {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }
        return fetch(url, {
            method: 'GET',
            headers: headers,
            credentials: 'same-origin',
        }).then(async r => {
            if (!r.ok) {
                const data = await r.json().catch(() => ({ success: false, message: `HTTP ${r.status}` }));
                throw new Error(data.message || `HTTP ${r.status}`);
            }
            return r.json();
        });
    }

    handleNotify(e) {
        if (e) e.preventDefault();
        this.runActionWithCooldown(this.elements.btnNotify, () =>
            this.getJson(this.routes.notify)
                .then((data) => {
                    if (data && data.success) {
                        this.playNotificationSound();
                        this.fetchData();
                    } else {
                        throw new Error(data?.message || 'Notification failed');
                    }
                })
                .catch((err) => {
                    console.error('Notify error:', err);
                    alert('Failed to notify customer: ' + (err.message || 'Unknown error'));
                    this.fetchData();
                })
        );
    }

    handleSkip(e) {
        this.closeSkipModal();
        this.runActionWithCooldown(e.target, () =>
            this.getJson(this.routes.skip)
                .then(() => this.fetchData())
        );
    }

    handleComplete(e) {
        this.runActionWithCooldown(e.target, () =>
            this.getJson(this.routes.complete)
                .then(() => this.fetchData())
        );
    }

    handleCallNext(e) {
        this.runActionWithCooldown(e.target, () =>
            this.getJson(this.routes.callNext)
                .then(() => {
                    this.playNotificationSound();
                    this.fetchData();
                })
        );
    }

    handleRecall(id) {
        this.getJson(`${this.routes.recall}?queue_id=${id}`)
            .then((res) => {
                if (!res || res.success !== true) {
                    throw new Error((res && res.message) ? res.message : 'Recall failed. Please try again.');
                }
                this.playNotificationSound();
                this.fetchData();
            })
            .catch((err) => {
                alert(err?.message || 'Recall failed. Please try again.');
            });
    }

    openSkipModal() {
        this.elements.skipModal.classList.remove('hidden');
        setTimeout(() => {
            this.elements.skipModal.classList.add('opacity-100');
            this.elements.skipModalContent.classList.remove('scale-95', 'opacity-0');
            this.elements.skipModalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    closeSkipModal() {
        this.elements.skipModalContent.classList.remove('scale-100', 'opacity-100');
        this.elements.skipModalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            this.elements.skipModal.classList.add('hidden');
        }, 300);
    }

    openTransferModal() {
        const idToTransfer = this.state.currentQueue ? this.state.currentQueue.id : null;
        if (!idToTransfer) {
            alert('No queue to transfer');
            return;
        }
        this.state.selectedTransferQueueId = idToTransfer;

        if (this.state.onlineCounters.length === 0) {
            alert('No available counters to transfer to');
            return;
        }

        this.elements.countersList.innerHTML = this.state.onlineCounters.map(counter => `
            <button type="button" data-counter-id="${counter.id}" class="w-full p-3 border-2 border-gray-200 hover:border-blue-500 hover:bg-blue-50 rounded-lg text-left transition">
                <div class="font-semibold text-gray-800">Counter ${counter.counter_number}</div>
                <div class="text-sm text-gray-600">${counter.display_name}</div>
            </button>
        `).join('');

        this.elements.countersList.querySelectorAll('button').forEach(button => {
            button.addEventListener('click', (e) => this.handleTransfer(e.currentTarget.dataset.counterId));
        });

        this.elements.transferModal.classList.remove('hidden');
        setTimeout(() => {
            this.elements.transferModal.classList.add('opacity-100');
            this.elements.transferModalContent.classList.remove('scale-95', 'opacity-0');
            this.elements.transferModalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    closeTransferModal() {
        this.elements.transferModalContent.classList.remove('scale-100', 'opacity-100');
        this.elements.transferModalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            this.elements.transferModal.classList.add('hidden');
        }, 300);
    }

    handleTransfer(toCounterId) {
        if (!this.state.selectedTransferQueueId) {
            alert('No queue to transfer');
            this.closeTransferModal();
            return;
        }
        this.closeTransferModal();

        fetch(`${this.routes.transfer}?queue_id=${this.state.selectedTransferQueueId}&to_counter_id=${toCounterId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || `HTTP ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                this.state.selectedTransferQueueId = null;
                this.fetchData();
            } else {
                alert('Transfer failed: ' + (data.message || 'Unknown error'));
                this.state.selectedTransferQueueId = null;
                this.fetchData();
            }
        })
        .catch(err => {
            console.error('Transfer error:', err);
            alert('Transfer failed: ' + err.message);
            this.state.selectedTransferQueueId = null;
            this.fetchData();
        });
    }

    setMinimized(minimized) {
        this.state.isMinimized = !!minimized;
        if (this.state.isMinimized) {
            this.elements.panelHeader.classList.add('hidden');
            this.elements.panelMain.classList.add('hidden');
            this.elements.panelDock.classList.remove('hidden');
            this.closeSkipModal();
            this.closeTransferModal();
        } else {
            this.elements.panelHeader.classList.remove('hidden');
            this.elements.panelMain.classList.remove('hidden');
            this.elements.panelDock.classList.add('hidden');
        }
        this.elements.btnToggleMinimize.title = this.state.isMinimized ? 'Restore' : 'Minimize';
        this.elements.btnToggleMinimize.innerHTML = this.state.isMinimized ? '<i class="fas fa-window-restore"></i>' : '<i class="fas fa-window-minimize"></i>';
        localStorage.setItem('counterPanelMinimized', this.state.isMinimized ? '1' : '0');
    }

    toggleMinimize(force) {
        if (typeof force === 'boolean') {
            this.setMinimized(force);
            return;
        }
        this.setMinimized(!this.state.isMinimized);
    }

    loadMinimizedState() {
        const saved = localStorage.getItem('counterPanelMinimized');
        if (saved === '1') this.setMinimized(true);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const panelElement = document.getElementById('counter-panel');
    if (panelElement) {
        new CounterPanel(panelElement);
    }
});