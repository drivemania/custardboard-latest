@php
    $themeUrl = $base_path . '/public/themes/' . $group->theme;
    $name = $group->name ?? 'CUSTARD-BOARD';
@endphp
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:title" content="{{ $group->name }}">
    <meta property="og:description" content="{{ !empty($group->description) ? $group->description : "" }}">
    <meta property="og:image" content="{{ !empty($group->og_image) ? $base_path . '/public' . $group->og_image : "" }}">
    <title>@yield('title', $name)</title>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    
    <script src="https://unpkg.com/swup@4"></script>
    <script src="https://unpkg.com/@swup/head-plugin@2"></script>
    <script src="https://unpkg.com/@swup/scripts-plugin@2"></script>
    <script src="https://unpkg.com/@swup/forms-plugin@3"></script>

    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js" ></script>

    @if(!empty($group->favicon))
    <link rel="icon" href="{{ $base_path }}{{ $group->favicon }}">
    @else
    <link rel="icon" href="{{ $base_path }}/public/favicon.ico">
    @endif

    <style>
        [x-cloak] { display: none !important; }

        .transition-fade {
            transition: 0.3s;
            opacity: 1;
        }
        html.is-animating .transition-fade {
            opacity: 0;
            transform: translateY(10px);
        }

        #global-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #ffffff;
            z-index: 99999;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: opacity 0.5s ease-out, visibility 0.5s;
        }
        
        #global-loader.is-hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .loader-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #2e2e2e;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    @hook('layout_head')
    @stack('styles')
</head>
<body>
    
    <div id="global-loader">
        <div class="flex flex-col items-center gap-3">
            <div class="loader-spinner"></div>
            <p class="text-sm text-gray-400 font-bold animate-pulse">Loading...</p>
        </div>
    </div>

    <div id="custard-notification-area" class="fixed top-20 left-1/2 -translate-x-1/2 z-[9999] w-80 pointer-events-none flex flex-col items-center">
    </div>

    <main id="swup" class="transition-fade" x-ignore>
        @if(isset($_SESSION['flash_message']))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" 
                 class="fixed top-5 right-5 z-50 min-w-[300px] bg-white border-l-4 p-4 shadow-lg rounded 
                 {{ $_SESSION['flash_type'] == 'error' ? 'border-red-500 text-red-700' : 'border-green-500 text-green-700' }}">
                 <p class="font-bold">{{ $_SESSION['flash_type'] == 'error' ? '오류' : '성공' }}</p>
                 <button @click="show = false" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                 </button>
                 <p>{{ $_SESSION['flash_message'] }}</p>
            </div>
            @php unset($_SESSION['flash_message'], $_SESSION['flash_type']); @endphp
        @endif

        @yield('theme_content')
        
    </main>

    <div id="hidden-player" style="position: absolute; top: -9999px; left: -9999px; opacity: 0; pointer-events: none;">
        <div id="yt-player"></div>
    </div>
    
    <script data-swup-ignore-script>
        window.addEventListener('load', function() {
            const loader = document.getElementById('global-loader');
            if (loader) {
                loader.classList.add('is-hidden');
            }
        });

        const swup = new Swup({
            containers: ["#swup"],
            linkToSelf: 'navigate',
            cache: false,
            plugins: [
                new SwupHeadPlugin({
                    persistTags: (tag) => tag.tagName === 'STYLE'
                }),
                new SwupScriptsPlugin({ head: false, body: true }),
                new SwupFormsPlugin({
                    formSelector: 'form[data-swup-form], form:not([data-no-swup])'
                })
            ],
            ignoreVisit: (url, { el } = {}) => {
                if (url.includes('/admin')) return true;
                if (url.includes('/logout')) return true;
                if (el?.matches('[data-no-swup]')) return true;
                return false;
            }
        });


        function waitForStyle(fileName = 'style.css', maxWait = 2000) {
            return new Promise(resolve => {
                const allLinks = document.querySelectorAll('link[rel="stylesheet"]');
                const targetLinks = Array.from(allLinks).filter(link => link.href.includes(fileName));

                if (targetLinks.length === 0) {
                    return resolve();
                }

                const loadPromises = targetLinks.map(link => {
                    if (link.sheet) return Promise.resolve();

                    return new Promise(innerResolve => {
                        link.onload = innerResolve;
                        link.onerror = innerResolve; 
                    });
                });

                const timeoutPromise = new Promise(timeoutResolve => {
                    setTimeout(timeoutResolve, maxWait);
                });

                Promise.race([Promise.all(loadPromises), timeoutPromise]).then(resolve);
            });
        }

        function nextFrame() {
            return new Promise(resolve => {
                requestAnimationFrame(() => {
                    requestAnimationFrame(resolve);
                });
            });
        }

        swup.hooks.on('visit:start', () => {
            const loader = document.getElementById('global-loader');
            if (loader) loader.classList.remove('is-hidden');
        });

        swup.hooks.on('page:view', async () => {
            const swupContainer = document.querySelector('#swup');
            if (swupContainer && window.Alpine) {
                swupContainer.removeAttribute('x-ignore');
                await nextFrame();
                window.Alpine.initTree(swupContainer);
            }
            
            const StyleLoadPromise = waitForStyle();
            const timeoutPromise = new Promise(resolve => setTimeout(resolve, 3000));
            
            await Promise.race([StyleLoadPromise, timeoutPromise]);

            const loader = document.getElementById('global-loader');
            if (loader) {
                loader.classList.add('is-hidden');
            }
        });

        document.addEventListener('alpine:init', () => {
            const swupContainer = document.querySelector('#swup');
            if (swupContainer) {
                swupContainer.removeAttribute('x-ignore');
            }
        });

        @if(!empty($group->playlist))
        if (!window.YT) {
            var tag = document.createElement('script');
            tag.src = "https://www.youtube.com/iframe_api";
            var firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
        }

        if (!window.MusicController) {
            window.MusicController = {
                player: null,
                isPlaying: false,
                playlistId: '{{ $group->playlist }}',
                
                onReady: function(event) {
                    this.updateUI();
                    this.restorePosition();
                    this.player.setLoop();
                },

                onStateChange: function(event) {
                    if (event.data === YT.PlayerState.PLAYING) {
                        this.isPlaying = true;
                        this.updateUI(true);
                    } else if (event.data === YT.PlayerState.PAUSED) {
                        this.isPlaying = false;
                        this.updateUI();
                    }
                },

                toggle: function() {
                    if (!this.player) return;
                    if (this.isPlaying) {
                        this.player.pauseVideo();
                    } else {
                        this.player.playVideo();
                    }
                },

                next: function() {
                    if (this.player) this.player.nextVideo();
                },

                prev: function() {
                    if (this.player) this.player.previousVideo();
                },

                setVolume: function(val) {
                    if (this.player) this.player.setVolume(val);
                },

                updateUI: function(fetchTitle = false) {
                    const icon = this.isPlaying ? '⏸' : '▶';
                    const status = this.isPlaying ? 'Now Playing' : 'Paused';

                    const pcIcon = document.getElementById('mp-pc-play-icon') ?? 0;
                    const pcTitle = document.getElementById('mp-pc-title') ?? 0;
                    const pcStatus = document.getElementById('mp-pc-status') ?? 0;
                    
                    if (pcIcon) pcIcon.innerText = icon;
                    if (pcStatus) pcStatus.innerText = status;
                    if (pcTitle && fetchTitle && this.player && this.player.getVideoData) {
                        const data = this.player.getVideoData();
                        if (data && data.title) pcTitle.innerText = data.title;
                    }

                    const mobileIcon = document.getElementById('mp-mobile-play-icon');
                    if (mobileIcon) mobileIcon.innerText = icon;
                },

                initDraggable: function() {
                    const wrapper = document.getElementById('mp-pc-wrapper');
                    const handle = document.getElementById('mp-drag-target');
                    
                    if (!wrapper || !handle) return;

                    this.restorePosition();

                    let isDragging = false;
                    let startX, startY, initialLeft, initialTop;

                    handle.addEventListener('mousedown', (e) => {
                        isDragging = true;
                        const rect = wrapper.getBoundingClientRect();
                        
                        wrapper.style.right = 'auto';
                        wrapper.style.bottom = 'auto';
                        wrapper.style.left = rect.left + 'px';
                        wrapper.style.top = rect.top + 'px';

                        startX = e.clientX;
                        startY = e.clientY;
                        initialLeft = rect.left;
                        initialTop = rect.top;
                        
                        document.body.style.userSelect = 'none';
                    });

                    window.addEventListener('mousemove', (e) => {
                        if (!isDragging) return;
                        const dx = e.clientX - startX;
                        const dy = e.clientY - startY;
                        
                        wrapper.style.left = `${initialLeft + dx}px`;
                        wrapper.style.top = `${initialTop + dy}px`;
                    });

                    window.addEventListener('mouseup', () => {
                        if (!isDragging) return;
                        isDragging = false;
                        document.body.style.userSelect = '';
                        
                        localStorage.setItem('mp_pos_left', wrapper.style.left);
                        localStorage.setItem('mp_pos_top', wrapper.style.top);
                    });
                },

                restorePosition: function() {
                    const wrapper = document.getElementById('mp-pc-wrapper');
                    if (!wrapper) return;

                    const savedLeft = localStorage.getItem('mp_pos_left');
                    const savedTop = localStorage.getItem('mp_pos_top');

                    if (savedLeft && savedTop) {
                        wrapper.style.right = 'auto';
                        wrapper.style.bottom = 'auto';
                        wrapper.style.left = savedLeft;
                        wrapper.style.top = savedTop;
                    }
                }
            };
        }

        window.onYouTubeIframeAPIReady = function() {
            window.MusicController.player = new YT.Player('yt-player', {
                height: '200',
                width: '200',
                playerVars: {
                    'listType': 'playlist',
                    'list': window.MusicController.playlistId,
                    'autoplay': 0,
                    'controls': 0,
                    'disablekb': 1,
                    'fs': 0
                },
                events: {
                    'onReady': window.MusicController.onReady.bind(window.MusicController),
                    'onStateChange': window.MusicController.onStateChange.bind(window.MusicController)
                }
            });
        };

        if (typeof swup !== 'undefined') {
            swup.hooks.on('page:view', () => {
                setTimeout(() => {
                    if (window.MusicController) {
                        window.MusicController.updateUI(true);
                        window.MusicController.initDraggable();
                    }
                }, 100);
            });
        }
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                if(window.MusicController) window.MusicController.initDraggable();
            }, 500);
        });
        @endif
    </script>
    <script>
         document.addEventListener('DOMContentLoaded', () => {
            
            @if(($group->use_notification ?? 1) == 0) return; @endif

            const displayedIds = new Set();

            function checkNotifications() {
                fetch('{{ $base_path }}/api/notifications/check')
                    .then(res => {
                        if(res.ok) return res.json();
                        throw new Error('Network response was not ok');
                    })
                    .then(data => {
                        if (data.notifications && data.notifications.length > 0) {
                            data.notifications.forEach(noti => {
                                if (!displayedIds.has(noti.id)) {
                                    showBubble(noti);
                                    displayedIds.add(noti.id);
                                }
                            });
                        }
                    })
                    .catch(error => console.error("Notification Error:", error));
            }

            function markAsRead(id) {
                fetch('{{ $base_path }}/api/notifications/read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                }).catch(err => console.error(err));
            }

            function showBubble(noti) {
                const area = document.getElementById('custard-notification-area');
                const bubble = document.createElement('div');
                
                bubble.className = "pointer-events-auto cursor-pointer relative flex items-center w-full bg-white border-2 border-amber-500 text-gray-800 px-4 py-3 rounded-2xl shadow-xl mb-3 transition-all duration-300 opacity-0 -translate-y-4 pr-8"; 
                
                let iconHtml = '';
                
                if (noti.char_img) {
                    iconHtml = `<div class="w-10 h-10 rounded-full border border-gray-200 overflow-hidden mr-3 shrink-0">
                                    <img src="${noti.char_img}" class="w-full h-full object-cover">
                                </div>`;
                } else {
                    let icon = '📩'; 
                    if(noti.type === 'comment') icon = '💬';
                    iconHtml = `<div class="mr-3 text-2xl">${icon}</div>`;
                }

                const closeBtnHtml = `
                    <button type="button" class="close-btn absolute top-2 right-2 text-gray-400 hover:text-gray-600 p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                `;

                bubble.innerHTML = `
                    ${iconHtml}
                    <div class="flex-1 text-sm font-bold break-keep leading-tight">${noti.message}</div>
                    ${closeBtnHtml}
                `;

                bubble.onclick = function(e) {
                    markAsRead(noti.id);
                    
                    if (noti.url) {
                        if(noti.type === 'memo') {
                            window.open('{{ $base_path }}/memo', 'memo', 'width=650,height=700');
                        } else {
                            if (typeof swup !== 'undefined') {
                                swup.navigate('{{ $base_path }}' + noti.url);
                            } else {
                                location.href = '{{ $base_path }}' + noti.url;
                            }
                        }
                    }
                    removeBubble(bubble, noti.id);
                };

                const closeBtn = bubble.querySelector('.close-btn');
                if(closeBtn) {
                    closeBtn.onclick = function(e) {
                        e.stopPropagation();
                        markAsRead(noti.id);
                        removeBubble(bubble, noti.id);
                    }
                }

                area.appendChild(bubble);
                requestAnimationFrame(() => {
                    bubble.classList.remove('opacity-0', '-translate-y-4');
                    bubble.classList.add('opacity-100', 'translate-y-0');
                });
            }

            function removeBubble(el, id) {
                if(id) displayedIds.delete(id);

                el.classList.remove('opacity-100', 'translate-y-0');
                el.classList.add('opacity-0', '-translate-y-4');
                
                setTimeout(() => {
                    if(el.parentNode) el.parentNode.removeChild(el);
                }, 300);
            }

            @if(isset($_SESSION['user_idx']))
                checkNotifications();
                setInterval(checkNotifications, 5000);
            @endif
        });

        
    </script>
    
</body>
</html>