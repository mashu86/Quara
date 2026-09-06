@if(($showLastLuckyDraw ?? '0') === '1' && isset($latestLuckyDraw) && $latestLuckyDraw && $latestLuckyDraw->winners->count() > 0)
    @php
        // Mask Phone Helper: Shows first 2 and last 2 digits, masks middle with 5 asterisks (e.g. 98*****41)
        $maskPhone = function($phone) {
            $digits = preg_replace('/\D/', '', $phone ?? '');
            $len = strlen($digits);
            if ($len < 4) return '****';
            $first = substr($digits, 0, 2);
            $last = substr($digits, -2);
            return $first . '*****' . $last;
        };

        // Mask Email Helper: Shows first 2 chars of username, masks rest before @ (e.g. ra***a@gmail.com)
        $maskEmail = function($email) {
            if (!$email || !str_contains($email, '@')) return null;
            $parts = explode('@', $email, 2);
            $user = $parts[0];
            $domain = $parts[1] ?? '';
            $uLen = strlen($user);
            if ($uLen <= 2) {
                $maskedUser = substr($user, 0, 1) . '***';
            } else {
                $maskedUser = substr($user, 0, 2) . '***' . substr($user, -1);
            }
            return $maskedUser . '@' . $domain;
        };
    @endphp

    <style>
        #latestLuckyWinnersModal .modal-dialog {
            margin: 0.75rem auto;
            max-width: 720px;
        }
        #latestLuckyWinnersModal .modal-content {
            border-radius: 20px !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25) !important;
        }
        #latestLuckyWinnersModal .modal-header {
            background: linear-gradient(135deg, #111111 0%, #1A1A1A 100%);
            border-bottom: 3px solid var(--qw-gold, #D4AF37) !important;
            position: relative;
            padding-right: 3rem !important;
        }
        #latestLuckyWinnersModal .header-close-btn {
            position: absolute;
            top: 14px;
            right: 14px;
            z-index: 10;
            opacity: 0.85;
            transition: opacity 0.2s ease;
        }
        #latestLuckyWinnersModal .header-close-btn:hover {
            opacity: 1;
        }
        #latestLuckyWinnersModal .winner-card {
            background: #ffffff;
            padding: 1.15rem 1.25rem !important;
            border-radius: 16px !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        #latestLuckyWinnersModal .winner-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08) !important;
        }
        #latestLuckyWinnersModal .promo-banner {
            margin-top: 1.6rem !important;
            margin-bottom: 1.25rem !important;
            padding: 1rem 1.25rem !important;
            border-radius: 16px !important;
        }

        /* Responsive Mobile Optimizations for 320px - 576px screens */
        @media (max-width: 575.98px) {
            #latestLuckyWinnersModal .modal-dialog {
                margin: 0.4rem auto !important;
                width: calc(100% - 0.8rem) !important;
            }
            #latestLuckyWinnersModal .modal-header {
                padding: 0.75rem 2.6rem 0.75rem 0.75rem !important;
            }
            #latestLuckyWinnersModal .header-icon-box {
                width: 36px !important;
                height: 36px !important;
                min-width: 36px !important;
                font-size: 0.95rem !important;
            }
            #latestLuckyWinnersModal .giveaway-badge {
                font-size: 0.58rem !important;
                padding: 0.2rem 0.5rem !important;
                letter-spacing: 0.5px !important;
            }
            #latestLuckyWinnersModal .modal-title {
                font-size: 0.92rem !important;
                line-height: 1.25 !important;
            }
            #latestLuckyWinnersModal .header-subtitle {
                font-size: 0.65rem !important;
                line-height: 1.2 !important;
            }
            #latestLuckyWinnersModal .header-close-btn {
                top: 10px !important;
                right: 10px !important;
            }
            #latestLuckyWinnersModal .modal-body {
                padding: 0.85rem 0.75rem 1rem 0.75rem !important;
                max-height: 76vh;
                overflow-y: auto;
            }
            #latestLuckyWinnersModal .congrats-wrapper {
                margin-bottom: 1.15rem !important;
            }
            #latestLuckyWinnersModal .congrats-badge {
                font-size: 0.68rem !important;
                padding: 0.4rem 0.75rem !important;
                white-space: normal !important;
                line-height: 1.35 !important;
            }
            #latestLuckyWinnersModal .winner-card {
                padding: 0.85rem 0.75rem !important;
                border-radius: 14px !important;
            }
            #latestLuckyWinnersModal .winner-avatar-badge {
                width: 36px !important;
                height: 36px !important;
                min-width: 36px !important;
                font-size: 0.88rem !important;
            }
            #latestLuckyWinnersModal .winner-pos-badge {
                font-size: 0.6rem !important;
                padding: 0.15rem 0.45rem !important;
            }
            #latestLuckyWinnersModal .verified-badge {
                font-size: 0.58rem !important;
                padding: 0.15rem 0.45rem !important;
            }
            #latestLuckyWinnersModal .winner-name {
                font-size: 0.88rem !important;
                margin-top: 1px !important;
                margin-bottom: 3px !important;
            }
            #latestLuckyWinnersModal .winner-address {
                font-size: 0.72rem !important;
                line-height: 1.35 !important;
                word-break: break-word !important;
                white-space: normal !important;
            }
            #latestLuckyWinnersModal .contact-pill {
                font-size: 0.64rem !important;
                padding: 2px 6px !important;
                max-width: 100% !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }
            #latestLuckyWinnersModal .promo-banner {
                padding: 0.75rem 0.65rem !important;
                margin-top: 1.15rem !important;
                margin-bottom: 0.75rem !important;
                border-radius: 12px !important;
            }
            #latestLuckyWinnersModal .promo-title {
                font-size: 0.75rem !important;
                line-height: 1.3 !important;
            }
            #latestLuckyWinnersModal .promo-malayalam {
                font-size: 0.75rem !important;
                line-height: 1.35 !important;
            }
            #latestLuckyWinnersModal .modal-footer {
                padding: 0.65rem 0.85rem !important;
            }
            #latestLuckyWinnersModal .shop-btn {
                font-size: 0.82rem !important;
                padding: 0.55rem 1rem !important;
            }
        }
    </style>

    <!-- Latest Lucky Draw Winners Modal -->
    <div class="modal fade" id="latestLuckyWinnersModal" tabindex="-1" aria-labelledby="latestLuckyWinnersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg overflow-hidden">
                
                <!-- Modal Header -->
                <div class="modal-header border-0 text-white">
                    <div class="d-flex align-items-center gap-2 gap-md-3 min-w-0 flex-grow-1">
                        <div class="rounded-circle bg-warning text-dark p-2 p-md-3 fs-4 flex-shrink-0 d-flex align-items-center justify-content-center shadow-sm header-icon-box" style="width: 44px; height: 44px; background-color: var(--qw-gold) !important;">
                            <i class="fa-solid fa-trophy"></i>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <span class="badge bg-gold text-white rounded-pill px-2 py-0.5 text-uppercase fw-bold mb-1 giveaway-badge" style="font-size: 0.62rem; letter-spacing: 0.5px;">
                                Official Giveaway Winners
                            </span>
                            <h5 class="modal-title font-serif fw-bold text-white mb-0 text-truncate" id="latestLuckyWinnersModalLabel">
                                {{ $latestLuckyDraw->title ? '🏆 ' . $latestLuckyDraw->title : 'Last Lucky Draw Winners!' }}
                            </h5>
                            <small class="text-white-50 header-subtitle text-truncate d-block" title="{{ $latestLuckyDraw->period_label }}">{{ $latestLuckyDraw->period_label }} • {{ $latestLuckyDraw->drawn_at ? $latestLuckyDraw->drawn_at->format('d M Y') : '' }}</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white header-close-btn" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-3.5 p-md-4 bg-light">
                    
                    <div class="text-center mb-3 mb-md-4 congrats-wrapper">
                        <span class="badge bg-dark text-warning rounded-pill px-3 py-1.5 fs-7 border border-warning congrats-badge d-inline-block shadow-xs">
                            <i class="fa-solid fa-crown text-warning me-1"></i> Congratulations to our {{ $latestLuckyDraw->winners->count() }} Lucky Winners!
                        </span>
                    </div>

                    <!-- Winners List Grid -->
                    <div class="row g-3 g-md-3.5">
                        @foreach($latestLuckyDraw->winners as $winner)
                            @php
                                $positionBadgeClass = 'bg-secondary';
                                $positionIcon = 'fa-award';
                                if($winner->position === 1) {
                                    $positionBadgeClass = 'bg-warning text-dark';
                                    $positionIcon = 'fa-crown';
                                } elseif($winner->position === 2) {
                                    $positionBadgeClass = 'bg-secondary text-white';
                                    $positionIcon = 'fa-medal';
                                } elseif($winner->position === 3) {
                                    $positionBadgeClass = 'bg-dark text-warning border border-warning';
                                    $positionIcon = 'fa-trophy';
                                }
                            @endphp
                            <div class="col-12 col-md-6">
                                <div class="card h-100 border rounded-4 shadow-sm position-relative overflow-hidden winner-card p-3 p-md-3.5">
                                    <div class="d-flex align-items-start gap-2.5 gap-md-3">
                                        <div class="badge {{ $positionBadgeClass }} rounded-circle p-2 flex-shrink-0 d-flex align-items-center justify-content-center shadow-xs winner-avatar-badge" style="width: 42px; height: 42px; font-size: 1.05rem;">
                                            <i class="fa-solid {{ $positionIcon }}"></i>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <!-- Position Badge & Verified Badge Inline Next to Each Other with Clear Gap -->
                                            <div class="d-flex align-items-center gap-2 gap-md-2.5 mb-1.5 flex-wrap winner-badges-row">
                                                <span class="badge bg-dark text-warning rounded-pill winner-pos-badge me-1" style="font-size: 0.65rem; padding: 3px 8px;">Winner #{{ $winner->position }}</span>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill verified-badge" style="font-size: 0.62rem; padding: 2px 7px;"><i class="fa-solid fa-circle-check me-1"></i>Verified</span>
                                            </div>

                                            <h6 class="fw-bold text-dark mb-1 text-truncate winner-name" style="font-size: 0.95rem;" title="{{ $winner->customer_name }}">{{ $winner->customer_name }}</h6>
                                            
                                            <!-- Full Address (No limit / No truncation) -->
                                            <div class="small text-muted mb-2 winner-address" style="font-size: 0.76rem; line-height: 1.4; word-break: break-word; white-space: normal;">
                                                <i class="fa-solid fa-location-dot text-danger me-1 flex-shrink-0"></i>{{ $winner->customer_address }}
                                            </div>

                                            <div class="d-flex flex-wrap gap-1.5 align-items-center mt-2 pt-2 border-top">
                                                @php
                                                    $rawElig = is_array($winner->eligibility) ? $winner->eligibility : json_decode($winner->eligibility, true);
                                                    $rawPhone = $rawElig['customer_phone'] ?? '';
                                                    $rawEmail = $rawElig['customer_email'] ?? '';
                                                @endphp

                                                @if($rawPhone)
                                                    <span class="badge bg-light text-dark border contact-pill px-2 py-1">
                                                        <i class="fa-solid fa-phone me-1 text-primary"></i> {{ $maskPhone($rawPhone) }}
                                                    </span>
                                                @endif

                                                @if($rawEmail)
                                                    <span class="badge bg-light text-dark border contact-pill px-2 py-1">
                                                        <i class="fa-solid fa-envelope me-1 text-warning"></i> {{ $maskEmail($rawEmail) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- PROMOTIONAL ENCOURAGEMENT BADGE -->
                    <div class="p-3 my-3 mt-4 rounded-4 text-center shadow-sm promo-banner" style="background: linear-gradient(135deg, #FFFDF0 0%, #FFF3CD 100%); border: 1.5px dashed #D4AF37; color: #7A5B00;">
                        <div class="d-flex align-items-center justify-content-center gap-1.5 mb-1 text-uppercase fw-bold promo-title" style="letter-spacing: 0.3px; font-size: 0.88rem;">
                            <i class="fa-solid fa-sparkles text-warning me-1"></i>
                            <span>You Could Be The Next Lucky Winner!</span>
                        </div>
                        <div class="fw-semibold promo-malayalam" style="font-size: 0.85rem; color: #5C4400;">
                            ✨ അടുത്ത തവണ ഭാഗ്യശാലി നിങ്ങളായിരിക്കാം! അതുകൊണ്ട് വേഗം ഓർഡർ ചെയ്യൂ!
                        </div>
                    </div>

                </div>

                <!-- Modal Footer CTA -->
                <div class="modal-footer border-0 bg-white p-3 justify-content-center">
                    <a href="{{ route('home') }}" class="btn btn-qw-gold rounded-pill px-4 py-2.5 fw-bold text-white w-100 shadow-sm d-flex align-items-center justify-content-center gap-2 shop-btn" style="font-size: 0.9rem;">
                        <i class="fa-solid fa-bag-shopping"></i> 🛍️ Shop Now & Join Next Draw
                    </a>
                </div>

            </div>
        </div>
    </div>
@endif
