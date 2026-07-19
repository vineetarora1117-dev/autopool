</main>

    </div>

    <!-- UI Interaction Scripts Architecture -->
    <script>
        const toggleMenuBtn = document.getElementById('toggleMenuBtn');
        const sidebarEl = document.querySelector('sidebar');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');

        function openSidebarMobile() {
            sidebarEl.classList.add('sidebar-open');
            sidebarBackdrop.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebarMobile() {
            sidebarEl.classList.remove('sidebar-open');
            sidebarBackdrop.classList.remove('show');
            document.body.style.overflow = '';
        }

        if (toggleMenuBtn && sidebarEl) {
            toggleMenuBtn.addEventListener('click', () => {
                if (window.innerWidth <= 900) {
                    if (sidebarEl.classList.contains('sidebar-open')) {
                        closeSidebarMobile();
                    } else {
                        openSidebarMobile();
                    }
                } else {
                    sidebarEl.classList.toggle('sidebar-hidden');
                }
            });
        }

        if (sidebarBackdrop) {
            sidebarBackdrop.addEventListener('click', closeSidebarMobile);
        }

        // Auto-close sidebar on mobile when a menu link is tapped
        if (sidebarEl) {
            sidebarEl.addEventListener('click', (e) => {
                if (window.innerWidth <= 900) {
                    const navItem = e.target.closest('.nav-item');
                    const submenuItem = e.target.closest('.submenu-item');
                    
                    if (navItem && navItem.classList.contains('sidebar-toggle')) {
                        return; // Do not close on dropdown click
                    }
                    
                    if (navItem || submenuItem) {
                        closeSidebarMobile();
                    }
                }
            });
        }

        function switchView(sectionId, clickedElement) {
            // 1. सभी कंटेंट सेक्शन्स से active-view क्लास हटाएं
            const sections = document.querySelectorAll('.content-section');
            sections.forEach(sec => sec.classList.remove('active-view'));

            // 2. सिलेक्टेड सेक्शन को स्क्रीन पर दिखाएं
            const target = document.getElementById(sectionId);
            if(target) target.classList.add('active-view');

            // 3. सभी मेनू आइटम से पुरानी 'active' क्लास हटाएं
            const mainNavItems = document.querySelectorAll('.nav-item');
            mainNavItems.forEach(item => item.classList.remove('active'));

            // 4. सभी सब-मेनू आइटम से पुरानी 'active-sub' क्लास हटाएं
            const subItems = document.querySelectorAll('.submenu-item');
            subItems.forEach(item => item.classList.remove('active-sub'));
            
            // 5. वर्तमान में क्लिक किए गए एलिमेंट को एक्टिव क्लास असाइन करें
            if (clickedElement) {
                if (clickedElement.classList.contains('submenu-item')) {
                    clickedElement.classList.add('active-sub');
                    clickedElement.closest('.submenu-container')?.previousElementSibling?.classList.add('active');
                } else if (clickedElement.classList.contains('nav-item')) {
                    clickedElement.classList.add('active');
                }
            } else {
                // अगर डायरेक्ट आईडी से कॉल हुआ हो (जैसे क्विक बटन्स से)
                if(sectionId === 'dashboardSection') {
                    document.querySelector('.nav-item').classList.add('active');
                }
            }

            // 6. हर सेक्शन को उसका अपना URL दें (एक ही फाइल में, अलग-अलग पेज जैसा अनुभव)
            if (target && window.location.hash !== '#' + sectionId) {
                history.pushState(null, '', '#' + sectionId);
            }
            window.scrollTo(0, 0);

            // Hook for Choice Modal Popups on Buy Packages
            if (sectionId === 'autopoolPackageSection' || sectionId === 'infinityPackageSection') {
                showChoiceModal(sectionId);
            }
        }

        let activeChoiceSection = '';
        function showChoiceModal(sectionId) {
            activeChoiceSection = sectionId;
            document.getElementById('buyChoiceModal').style.display = 'flex';
        }

        function handleChoice(choice) {
            document.getElementById('buyChoiceModal').style.display = 'none';
            const targetInputId = activeChoiceSection === 'autopoolPackageSection' ? 'autopoolMemberId' : 'infinityMemberId';
            const inputEl = document.getElementById(targetInputId);
            
            if (inputEl) {
                if (choice === 'self') {
                    inputEl.value = 'RJ129688';
                    inputEl.readOnly = true;
                    inputEl.classList.add('form-control-disabled');
                } else {
                    inputEl.value = '';
                    inputEl.readOnly = false;
                    inputEl.classList.remove('form-control-disabled');
                }
            }
        }

        // पेज लोड होने पर या ब्राउज़र के Back/Forward बटन दबाने पर सही सेक्शन खोलें
        function openSectionFromHash() {
            const id = window.location.hash.replace('#', '');
            if (id && document.getElementById(id)) {
                let navMatch = null;
                document.querySelectorAll('.nav-item, .submenu-item').forEach(el => {
                    const onclickAttr = el.getAttribute('onclick') || '';
                    if (onclickAttr.includes("'" + id + "'")) navMatch = el;
                });
                switchView(id, navMatch);
                if (navMatch && navMatch.classList.contains('submenu-item')) {
                    navMatch.closest('.submenu-container')?.classList.add('show');
                    navMatch.closest('.submenu-container')?.previousElementSibling?.classList.add('open');
                }
            }
        }
        window.addEventListener('popstate', openSectionFromHash);
        window.addEventListener('DOMContentLoaded', () => {
            if (window.location.hash) openSectionFromHash();
        });

        // साइडबार एकॉर्डियन मेनू ड्रॉपडाउन 
        const toggles = document.querySelectorAll('.sidebar-toggle');
        toggles.forEach(toggle => {
            toggle.addEventListener('click', () => {
                const targetId = toggle.getAttribute('data-target');
                const submenu = document.getElementById(targetId);
                toggle.classList.toggle('open');
                
                if (submenu.style.maxHeight && submenu.style.maxHeight !== '0px') {
                    submenu.style.maxHeight = '0px';
                } else {
                    submenu.style.maxHeight = submenu.scrollHeight + 'px';
                }
            });
        });

        // Fullscreen toggle (with vendor-prefixed fallbacks for older browsers)
        function toggleFullscreen() {
            const el = document.documentElement;
            const isFullscreen = document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement;

            if (!isFullscreen) {
                if (el.requestFullscreen) el.requestFullscreen();
                else if (el.webkitRequestFullscreen) el.webkitRequestFullscreen();
                else if (el.msRequestFullscreen) el.msRequestFullscreen();
            } else {
                if (document.exitFullscreen) document.exitFullscreen();
                else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
                else if (document.msExitFullscreen) document.msExitFullscreen();
            }
        }

        function updateFullscreenIcon() {
            const icon = document.querySelector('#fullscreenBtn i');
            if (!icon) return;
            const isFullscreen = document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement;
            icon.classList.toggle('fa-expand', !isFullscreen);
            icon.classList.toggle('fa-compress', !!isFullscreen);
        }

        document.addEventListener('fullscreenchange', updateFullscreenIcon);
        document.addEventListener('webkitfullscreenchange', updateFullscreenIcon);
        document.addEventListener('MSFullscreenChange', updateFullscreenIcon);

        // प्रोफाइल ड्रॉपडाउन हेडर ऐक्शन
        const profileBtn = document.getElementById('profileMenuBtn');
        const dropdownMenu = document.getElementById('profileDropdownMenu');

        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });

        document.addEventListener('click', () => {
            dropdownMenu.classList.remove('show');
        });

        // Global tap/ripple + glow feedback for every button-like element
        (function () {
            const tappableSelector = 'button, .db-action-btn, .btn-table-action, .nav-item, .submenu-item, .dropdown-item, .db-referral-btn';
            document.addEventListener('click', function (e) {
                const el = e.target.closest(tappableSelector);
                if (!el) return;

                const rect = el.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const ripple = document.createElement('span');
                ripple.className = 'ripple-effect';
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
                ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
                el.appendChild(ripple);
                setTimeout(() => ripple.remove(), 600);

                el.classList.add('tap-glow');
                setTimeout(() => el.classList.remove('tap-glow'), 280);
            });
        })();

        // --- 3D Cyber Fluid Engine (Three.js Background) — Enhanced ---
        let scene, camera, renderer, ambientLight;
        let clock = new THREE.Clock();
        const layers = []; // multiple particle layers with independent drift/twinkle
        let coreMesh, ringMesh, linesMesh;
        let mouseX = 0, mouseY = 0;

        // Custom shader material so particles twinkle (pulse) and have soft round glow,
        // instead of flat static dots.
        function makeTwinkleMaterial(color, baseSize) {
            return new THREE.ShaderMaterial({
                uniforms: {
                    uTime:  { value: 0 },
                    uColor: { value: new THREE.Color(color) },
                    uSize:  { value: baseSize }
                },
                vertexShader: `
                    attribute float aPhase;
                    attribute float aSpeed;
                    attribute float aScale;
                    uniform float uTime;
                    uniform float uSize;
                    varying float vTwinkle;
                    void main() {
                        vTwinkle = 0.55 + 0.45 * sin(uTime * aSpeed + aPhase);
                        vec4 mvPosition = modelViewMatrix * vec4(position, 1.0);
                        gl_PointSize = uSize * aScale * vTwinkle * (300.0 / -mvPosition.z);
                        gl_Position = projectionMatrix * mvPosition;
                    }
                `,
                fragmentShader: `
                    uniform vec3 uColor;
                    varying float vTwinkle;
                    void main() {
                        vec2 c = gl_PointCoord - vec2(0.5);
                        float d = length(c);
                        float glow = smoothstep(0.5, 0.0, d);
                        if (glow < 0.02) discard;
                        gl_FragColor = vec4(uColor, glow * vTwinkle);
                    }
                `,
                transparent: true,
                depthWrite: false,
                blending: THREE.AdditiveBlending
            });
        }

        function makeParticleLayer(count, spread, color, size, driftSpeed) {
            const geometry = new THREE.BufferGeometry();
            const positions = new Float32Array(count * 3);
            const phases = new Float32Array(count);
            const speeds = new Float32Array(count);
            const scales = new Float32Array(count);

            for (let i = 0; i < count; i++) {
                positions[i * 3]     = (Math.random() * 2 - 1) * spread.x;
                positions[i * 3 + 1] = (Math.random() * 2 - 1) * spread.y;
                positions[i * 3 + 2] = (Math.random() * 2 - 1) * spread.z;
                phases[i] = Math.random() * Math.PI * 2;
                speeds[i] = 0.6 + Math.random() * 1.8;
                scales[i] = 0.5 + Math.random() * 1.3;
            }

            geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
            geometry.setAttribute('aPhase', new THREE.BufferAttribute(phases, 1));
            geometry.setAttribute('aSpeed', new THREE.BufferAttribute(speeds, 1));
            geometry.setAttribute('aScale', new THREE.BufferAttribute(scales, 1));

            const material = makeTwinkleMaterial(color, size);
            const points = new THREE.Points(geometry, material);
            points.userData.driftSpeed = driftSpeed;
            points.userData.spread = spread;
            scene.add(points);
            layers.push(points);
            return points;
        }

        // Thin constellation lines connecting a subset of nearby foreground particles —
        // gives the background a "network / trading data" feel instead of plain rain.
        function makeConstellation(sourcePoints, maxLinks, maxDist) {
            const posAttr = sourcePoints.geometry.attributes.position;
            const linePositions = [];
            const total = posAttr.count;
            let links = 0;
            for (let i = 0; i < total && links < maxLinks; i += 3) {
                const ax = posAttr.getX(i), ay = posAttr.getY(i), az = posAttr.getZ(i);
                for (let j = i + 3; j < total && links < maxLinks; j += 7) {
                    const bx = posAttr.getX(j), by = posAttr.getY(j), bz = posAttr.getZ(j);
                    const dist = Math.hypot(ax - bx, ay - by, az - bz);
                    if (dist < maxDist) {
                        linePositions.push(ax, ay, az, bx, by, bz);
                        links++;
                    }
                }
            }
            const geometry = new THREE.BufferGeometry();
            geometry.setAttribute('position', new THREE.Float32BufferAttribute(linePositions, 3));
            const material = new THREE.LineBasicMaterial({
                color: 0xffb703,
                transparent: true,
                opacity: 0.12,
                blending: THREE.AdditiveBlending
            });
            const lines = new THREE.LineSegments(geometry, material);
            scene.add(lines);
            return lines;
        }

        function init() {
            const container = document.getElementById('canvas-container');
            scene = new THREE.Scene();
            scene.fog = new THREE.FogExp2(0x030b14, 0.0022);

            camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 1, 1000);
            camera.position.z = 65;
            camera.position.y = 12;
            camera.rotation.x = -0.12;

            ambientLight = new THREE.AmbientLight(0x222222);
            scene.add(ambientLight);

            let blueLight = new THREE.PointLight(0x00f2fe, 4, 400);
            blueLight.position.set(-120, 100, -60);
            scene.add(blueLight);

            let goldLight = new THREE.PointLight(0xffb703, 3.5, 400);
            goldLight.position.set(120, -100, -60);
            scene.add(goldLight);
            window.cyberLights = { blueLight, goldLight };

            renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
            renderer.setSize(window.innerWidth, window.innerHeight);
            container.appendChild(renderer.domElement);

            // Layer 1: distant gold haze (slow, large spread, small points)
            makeParticleLayer(700, { x: 420, y: 320, z: 260 }, 0xffb703, 26, 0.35);
            // Layer 2: mid cyan drifting field (adds depth + color contrast)
            makeParticleLayer(500, { x: 380, y: 280, z: 220 }, 0x00e5ff, 22, 0.55);
            // Layer 3: bright foreground gold sparks (faster, crisper)
            const foreground = makeParticleLayer(420, { x: 300, y: 220, z: 160 }, 0xffe57f, 30, 0.9);

            // Faint constellation network among the foreground sparks
            linesMesh = makeConstellation(foreground, 90, 55);

            // Slow-rotating wireframe icosahedron "core" — gives a focal 3D anchor
            const coreGeo = new THREE.IcosahedronGeometry(16, 1);
            const coreMat = new THREE.MeshBasicMaterial({
                color: 0xffb703,
                wireframe: true,
                transparent: true,
                opacity: 0.18
            });
            coreMesh = new THREE.Mesh(coreGeo, coreMat);
            coreMesh.position.set(0, -5, -140);
            scene.add(coreMesh);

            // Thin orbiting ring around the core for extra depth
            const ringGeo = new THREE.TorusGeometry(24, 0.25, 8, 100);
            const ringMat = new THREE.MeshBasicMaterial({
                color: 0x00e5ff,
                transparent: true,
                opacity: 0.25
            });
            ringMesh = new THREE.Mesh(ringGeo, ringMat);
            ringMesh.position.copy(coreMesh.position);
            ringMesh.rotation.x = Math.PI / 2.3;
            scene.add(ringMesh);

            window.addEventListener('resize', onWindowResize, false);
            window.addEventListener('mousemove', onMouseMove, false);
            animate();
        }

        function onMouseMove(e) {
            mouseX = (e.clientX / window.innerWidth - 0.5);
            mouseY = (e.clientY / window.innerHeight - 0.5);
        }

        function onWindowResize() {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        }

        function animate() {
            requestAnimationFrame(animate);
            const t = clock.getElapsedTime();

            // Drift + gently wrap each particle layer, with subtle per-layer sine sway
            layers.forEach((points) => {
                const positions = points.geometry.attributes.position.array;
                const spread = points.userData.spread;
                const speed = points.userData.driftSpeed;
                for (let i = 0; i < positions.length; i += 3) {
                    positions[i + 1] -= speed * 0.35;
                    positions[i] += Math.sin(t * 0.3 + positions[i + 2] * 0.01) * 0.03;
                    if (positions[i + 1] < -spread.y) {
                        positions[i + 1] = spread.y;
                    }
                }
                points.geometry.attributes.position.needsUpdate = true;
                points.rotation.y += 0.0006 * speed;
                points.material.uniforms.uTime.value = t;
            });

            // Slowly tumble the wireframe core + counter-rotate its ring
            if (coreMesh) {
                coreMesh.rotation.y += 0.0025;
                coreMesh.rotation.x += 0.0012;
                coreMesh.material.opacity = 0.14 + 0.06 * Math.sin(t * 0.5);
            }
            if (ringMesh) {
                ringMesh.rotation.z += 0.0018;
                ringMesh.material.opacity = 0.18 + 0.08 * Math.sin(t * 0.7 + 1);
            }
            if (linesMesh) {
                linesMesh.material.opacity = 0.08 + 0.05 * Math.sin(t * 0.4);
            }

            // Pulse the two point lights for a subtle breathing glow
            if (window.cyberLights) {
                window.cyberLights.blueLight.intensity = 3.2 + Math.sin(t * 0.6) * 1.2;
                window.cyberLights.goldLight.intensity = 2.8 + Math.cos(t * 0.5) * 1.1;
            }

            // Gentle parallax camera drift following the mouse — adds a "living" 3D feel
            camera.position.x += (mouseX * 18 - camera.position.x) * 0.02;
            camera.position.y += (12 - mouseY * 12 - camera.position.y) * 0.02;
            camera.lookAt(0, -5, -140);

            renderer.render(scene, camera);
        }

        init();
    </script>
    <?php
    $envConfig = parse_ini_file(__DIR__ . '/../.env');
    $siteUrl = rtrim($envConfig['SITE_URL'] ?? 'http://localhost/autopool', '/');
    ?>
    <style>
    .swal-btn-self {
        color: #000 !important;
        font-size: 16px !important;
        font-weight: 600 !important;
        min-width: 140px !important;
        padding: 12px 24px !important;
        margin: 5px !important;
    }
    .swal-btn-others {
        color: #fff !important;
        font-size: 16px !important;
        font-weight: 600 !important;
        min-width: 140px !important;
        padding: 12px 24px !important;
        margin: 5px !important;
    }
    </style>
    <script>
    function openBuyPackageFlow(type) {
        const siteUrl = '<?php echo $siteUrl; ?>';
        const pageName = type === 'autopool' ? 'autopoolPackage.php' : 'infinityPackage.php';
        const pageUrl = siteUrl + '/UserPanel/' + pageName;
        const verifyUrl = siteUrl + '/UserPanel/api/packages.php';
        
        Swal.fire({
            title: 'Purchase Option',
            text: 'Who are you purchasing this package for?',
            icon: 'question',
            showCloseButton: true,
            showCancelButton: false,
            showDenyButton: true,
            confirmButtonText: 'For Self',
            denyButtonText: 'For Others',
            confirmButtonColor: '#ffb703',
            denyButtonColor: '#005f73',
            background: '#1a1a2e',
            color: '#fff',
            customClass: {
                confirmButton: 'swal-btn-self',
                denyButton: 'swal-btn-others'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = pageUrl + '?target=self';
            } else if (result.isDenied) {
                Swal.fire({
                    title: 'Enter Target User ID',
                    input: 'text',
                    inputPlaceholder: 'e.g. SA123456',
                    showCancelButton: true,
                    confirmButtonText: 'Verify & Go',
                    confirmButtonColor: '#ffb703',
                    background: '#1a1a2e',
                    color: '#fff',
                    preConfirm: (userId) => {
                        if (!userId.trim()) {
                            Swal.showValidationMessage('User ID is required');
                            return false;
                        }
                        return fetch(verifyUrl + '?action=verify_user&user_id=' + encodeURIComponent(userId))
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (!data.success) {
                                    throw new Error(data.message || 'User ID is invalid or blocked');
                                }
                                return { userId: userId, name: data.name };
                            })
                            .catch(error => {
                                Swal.showValidationMessage(error.message);
                            });
                    }
                }).then((inputResult) => {
                    if (inputResult.isConfirmed && inputResult.value) {
                        const verifiedUser = inputResult.value;
                        Swal.fire({
                            title: 'Confirm Recipient',
                            html: `
                                <div style="text-align: left; padding: 10px 20px; font-size: 15px;">
                                    <p><strong>User ID:</strong> ${verifiedUser.userId}</p>
                                    <p><strong>Name:</strong> ${verifiedUser.name}</p>
                                </div>
                            `,
                            icon: 'info',
                            showCancelButton: true,
                            confirmButtonText: 'OK',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#ffb703',
                            cancelButtonColor: '#d33',
                            background: '#1a1a2e',
                            color: '#fff'
                        }).then((confirmRes) => {
                            if (confirmRes.isConfirmed) {
                                window.location.href = pageUrl + '?target=' + encodeURIComponent(verifiedUser.userId);
                            } else {
                                openBuyPackageFlow(type);
                            }
                        });
                    }
                });
            }
        });
    }
    </script>
</body>
</html>
