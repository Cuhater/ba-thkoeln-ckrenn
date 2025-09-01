// Initialize when DOM is ready or when content is injected later
(function () {

    class Game {
        constructor() {
            this.selectors = {
                boardContainer: document.querySelector('.board-container'),
                game: document.querySelector('.game'),
                board: document.querySelector('.board'),
                moves: document.querySelector('.moves'),
                timer: document.querySelector('.timer'),
                start: document.querySelector('button'),
                win: document.querySelector('.win')
            };
            this.state = {
                gameStarted: false,
                flippedCards: 0,
                totalFlips: 0,
                totalTime: 0,
                totalBonus: 0,
                loop: null
            };
            this.isProcessing = false;
            this.highscore = 0;
            this.attachEventListeners();
            this.generateGame();
        }

        shuffle(array) {
            const clonedArray = [...array];
            for (let index = clonedArray.length - 1; index > 0; index--) {
                const randomIndex = Math.floor(Math.random() * (index + 1));
                const original = clonedArray[index];
                clonedArray[index] = clonedArray[randomIndex];
                clonedArray[randomIndex] = original;
            }
            return clonedArray;
        }

        pickRandom(array, items) {
            const clonedArray = [...array];
            const randomPicks = [];
            for (let index = 0; index < items; index++) {
                const randomIndex = Math.floor(Math.random() * clonedArray.length);
                randomPicks.push(clonedArray[randomIndex]);
                clonedArray.splice(randomIndex, 1);
            }
            return randomPicks;
        }

        restartGame() {
            document.getElementById('modal-game-end').classList.add('hidden');
            document.querySelector('.board').innerHTML = '';
            document.querySelector('.picker').innerHTML = '';
            this.state.gameStarted = false;
            this.state.flippedCards = 0;
            this.state.totalFlips = 0;
            this.state.totalTime = 0;
            this.state.loop = null;
            clearInterval(this.state.loop);
            this.selectors.moves.innerText = `0 Karten umgedreht`;
            this.selectors.timer.innerText = `Zeit: 0 Sekunden`;
            this.generateGame();
        }

        async generateGame() {
            if (!this.selectors.game) return;

            // Ensure helper vars (provides logo, memory_images, pairs_count)
            let hv = null;
            try {
                hv = await (typeof ensureHelperVars === 'function' ? ensureHelperVars() : null);
            } catch (e) {}

            // Determine pairs and grid dimension
            const pairs = (hv && hv.pairs_count) ? parseInt(hv.pairs_count, 10) : parseInt(this.selectors.game.getAttribute('data-pairs') || '8', 10);
            let dim = Math.ceil(Math.sqrt(Math.max(2, pairs * 2)));
            if (dim % 2 !== 0) dim += 1; // ensure even for symmetry

            // Cardback image
            const dataBg = this.selectors.game.getAttribute('data-background') || '';
            const background = (hv && hv.logo) ? hv.logo : dataBg;

            // Memory images array of URLs
            let imageUrls = [];
            if (hv && Array.isArray(hv.memory_images) && hv.memory_images.length) {
                imageUrls = hv.memory_images.map((url, idx) => [url, idx]);
            } else {
                console.warn('Keine Memory-Bilder in helper_vars gefunden');
                return;
            }

            const needed = (dim * dim) / 2;
            const picks = this.pickRandom(imageUrls, Math.min(needed, imageUrls.length));
            const items = this.shuffle([...picks, ...picks]).slice(0, dim * dim);

            const cards = `
                <div class="board" style="grid-template-columns: repeat(${dim}, auto);">
                    ${items.map(item => `
                        <div class="card">
                            <div class="card-front" style="background-image:url('${background}')"></div>
                            <div class="card-back"><img alt="Karte" class="h-full w-full max-w-[128px] max-h-[128px] object-contain" src="${item[0]}">${item[1]}</div>
                        </div>
                    `).join('')}
                </div>`;
            const parser = new DOMParser().parseFromString(cards, 'text/html');
            document.querySelector('.board').replaceWith(parser.querySelector('.board'));
        }

        startGame() {
            this.state.gameStarted = true;
            clearInterval(this.state.loop);
            
            // Timestamp beim Spielstart setzen
            if (window.charigameHelper && typeof window.charigameHelper.setLastPlayedTimestamp === 'function') {
                window.charigameHelper.setLastPlayedTimestamp()
                    .catch(error => console.warn('Error setting last_played timestamp:', error));
            }
            
            this.state.loop = setInterval(() => {
                this.state.totalTime++;
                this.selectors.moves.innerText = `${this.state.totalFlips} Karten umgedreht`;
                this.selectors.timer.innerText = `Zeit: ${this.state.totalTime} Sekunden`;
            }, 1000);
        }

        flipBackCards() {
            document.querySelectorAll('.card:not(.matched)').forEach(card => {
                card.classList.remove('flipped');
            });
            this.state.flippedCards = 0;
        }

        flipCard(card) {
            if (this.isProcessing) return;
            this.state.flippedCards++;
            this.state.totalFlips++;
            if (!this.state.gameStarted) {
                this.startGame();
            }
            if (this.state.flippedCards <= 2) {
                card.classList.add('flipped');
            }
            if (this.state.flippedCards === 2) {
                const flippedCards = document.querySelectorAll('.flipped:not(.matched)');
                if (flippedCards[0].innerText === flippedCards[1].innerText) {
                    flippedCards[0].classList.add('matched');
                    flippedCards[1].classList.add('matched');
                }
                setTimeout(() => {
                    this.flipBackCards();
                    this.isProcessing = false;
                }, 1000);
            }
            if (!document.querySelectorAll('.card:not(.flipped)').length) {
                setTimeout(() => {
                    document.getElementById('game-points').innerHTML = this.state.totalFlips;
                    //document.getElementById('game-points-end').innerHTML = this.state.totalFlips;
                    document.getElementById('game-time').innerHTML = this.state.totalTime;
                    if (this.totalTime > 1) {
                        document.getElementById('game-time-unit').innerHTML = 'Sekunden';
                    } else {
                        document.getElementById('game-time-unit').innerHTML = 'Sekunden';
                    }
                    let totalBonus = setTotalBonus(this.state.totalFlips);
                    let formattedNumber = totalBonus.toFixed(2).replace('.', ',');
                    document.getElementById('personal-bonus').innerHTML = formattedNumber;
                    this.highscore = this.state.totalFlips;
                    let modal = document.getElementById('modal-game-end');
                    modal.classList.remove('hidden');
                    if (totalBonus < 1) {
                        document.getElementById('show-donation-triangle').classList.add('hidden');
                        document.getElementById('not-scored').classList.remove('hidden');
                        document.getElementById('scored').classList.add('hidden');
                    } else {
                        document.getElementById('show-donation-triangle').classList.remove('hidden');
                        document.getElementById('not-scored').classList.add('hidden');
                        document.getElementById('scored').classList.remove('hidden');
                    }
                    clearInterval(this.state.loop);
                }, 1000);
            }
        }

        attachEventListeners() {
            document.addEventListener('click', event => {
                const eventTarget = event.target;
                const eventParent = eventTarget.parentElement;
                if (eventTarget.className.includes('card') && !eventParent.className.includes('flipped')) {
                    this.flipCard(eventParent);
                } else if (eventTarget.classList.contains('btn-play-again')) {
                    this.restartGame();
                } else if (eventTarget.classList.contains('btn-play-again-end')) {
                } else if (eventTarget.className.includes('btn-show-donation')) {
                } else if (eventTarget.className.includes('btn-submit-score')) {
                }
            });
        }

        setTotalBonus(totalFlips) {

        }
    }

    // Initialize only once per page lifecycle
    function initMemoryGameOnce() {
        if (window.__charigameMemoryInitDone) return;
        const hasBoard = document.querySelector('.memory_game_grid .board');
        if (!hasBoard) return;
        window.__charigameMemoryInitDone = true;
        window.game = new Game();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMemoryGameOnce);
    } else {
        initMemoryGameOnce();
    }

    // Also observe DOM for injected content
    const observer = new MutationObserver(() => {
        if (!window.__charigameMemoryInitDone && document.querySelector('.memory_game_grid .board')) {
            initMemoryGameOnce();
        }
        if (window.__charigameMemoryInitDone) {
            observer.disconnect();
        }
    });
    observer.observe(document.documentElement || document.body, { childList: true, subtree: true });
})();
