@auth
<style>
    [x-cloak] { display: none !important; }
    .onboarding-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 36, 82, 0.7);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }
    .onboarding-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.3);
        width: 100%;
        max-width: 520px;
        margin: 16px;
        overflow: hidden;
        animation: onb-slide-up 0.4s ease-out;
    }
    @keyframes onb-slide-up {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div x-data="onboarding()" x-show="show" x-cloak class="onboarding-overlay">
    <div class="onboarding-card">
        {{-- Progress bar --}}
        <div style="height:4px;background:#E5E7EB;">
            <div style="height:100%;background:linear-gradient(90deg,#002452,#006c49);border-radius:4px;transition:width 0.5s ease;"
                 :style="'width:' + ((idx + 1) / totalSteps * 100) + '%'"></div>
        </div>

        {{-- Header --}}
        <div style="padding:20px 24px 12px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #F3F4F6;">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:28px;height:28px;border-radius:8px;background:#002452;display:flex;align-items:center;justify-content:center;">
                    <span class="material-symbols-outlined" style="color:#fff;font-size:16px;">school</span>
                </div>
                <span style="font-size:12px;font-weight:700;color:#002452;letter-spacing:0.5px;"
                      x-text="'GUIDE DE DÉMARRAGE - ' + (idx + 1) + '/' + totalSteps"></span>
            </div>
            <button @click="skip()" style="font-size:12px;color:#9CA3AF;background:none;border:none;cursor:pointer;display:flex;align-items:center;gap:4px;padding:4px 8px;border-radius:6px;transition:background 0.2s;"
                    onmouseover="this.style.background='#F3F4F6'" onmouseout="this.style.background='none'">
                <span class="material-symbols-outlined" style="font-size:14px;">close</span> Passer le guide
            </button>
        </div>

        {{-- Content area --}}
        <div style="padding:24px;min-height:300px;">

            {{-- Step: Bienvenue --}}
            <template x-if="step === 'welcome'">
                <div style="text-align:center;padding:16px 0;">
                    <div style="width:72px;height:72px;margin:0 auto 20px;border-radius:50%;background:linear-gradient(135deg,#002452,#003d7a);display:flex;align-items:center;justify-content:center;box-shadow:0 8px 24px rgba(0,36,82,0.3);">
                        <span class="material-symbols-outlined" style="color:#fff;font-size:36px;">waving_hand</span>
                    </div>
                    <h2 style="font-size:22px;font-weight:800;color:#002452;margin-bottom:8px;font-family:'Manrope',sans-serif;">Bienvenue sur EcoPoche !</h2>
                    <p style="font-size:14px;color:#6B7280;line-height:1.6;max-width:380px;margin:0 auto;">
                        Votre assistant de gestion budgétaire intelligent. Suivez ce guide pour configurer votre période, vos revenus et vos alertes.
                    </p>
                    <div style="margin-top:24px;display:flex;flex-wrap:wrap;justify-content:center;gap:8px;">
                        <span style="padding:6px 12px;border-radius:20px;background:#002452;color:#fff;font-size:11px;font-weight:600;">Budget</span>
                        <span style="padding:6px 12px;border-radius:20px;background:#006c49;color:#fff;font-size:11px;font-weight:600;">Épargne</span>
                        <span style="padding:6px 12px;border-radius:20px;background:#EF4444;color:#fff;font-size:11px;font-weight:600;">Alertes</span>
                        <span style="padding:6px 12px;border-radius:20px;background:#6366F1;color:#fff;font-size:11px;font-weight:600;">Période</span>
                        <span style="padding:6px 12px;border-radius:20px;background:#7C3AED;color:#fff;font-size:11px;font-weight:600;">Rapports</span>
                    </div>
                </div>
            </template>

            {{-- Step: Paramètres --}}
            <template x-if="step === 'params'">
                <div>
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
                        <div style="width:48px;height:48px;border-radius:14px;background:#002452;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span class="material-symbols-outlined" style="color:#fff;font-size:24px;">settings</span>
                        </div>
                        <div>
                            <h3 style="font-size:17px;font-weight:700;color:#1F2937;margin:0;">Paramètres</h3>
                            <p style="font-size:12px;color:#6B7280;margin:2px 0 0;">Première chose à faire : configurez vos préférences</p>
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#EEF2FF;border:1px solid #C7D2FE;">
                            <span class="material-symbols-outlined" style="color:#6366F1;font-size:20px;margin-top:2px;">date_range</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Mois budgétaire</strong> - Salaire le 25 ? Indiquez <strong>25</strong> : votre période ira du 25 au 24. Laissez vide pour le mois calendaire (1<sup>er</sup> au dernier jour).</div>
                        </div>
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#F0F4FF;border:1px solid #E0E7FF;">
                            <span class="material-symbols-outlined" style="color:#002452;font-size:20px;margin-top:2px;">tune</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Quota dépensable</strong> - Quel % de vos bonus est utilisable (le reste va en épargne).</div>
                        </div>
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#FEF2F2;border:1px solid #FECACA;">
                            <span class="material-symbols-outlined" style="color:#EF4444;font-size:20px;margin-top:2px;">warning</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Seuils d'alerte</strong> - Soyez averti avant de dépasser votre budget (70 % / 90 % par défaut).</div>
                        </div>
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#ECFDF5;border:1px solid #A7F3D0;">
                            <span class="material-symbols-outlined" style="color:#006c49;font-size:20px;margin-top:2px;">savings</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Objectif d'épargne</strong> - Fixez un pourcentage d'épargne automatique sur chaque période.</div>
                        </div>
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#F0F4FF;border:1px solid #E0E7FF;">
                            <span class="material-symbols-outlined" style="color:#002452;font-size:20px;margin-top:2px;">download</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Export RGPD</strong> - Téléchargez une copie JSON de toutes vos données à tout moment.</div>
                        </div>
                    </div>
                    <a href="{{ route('profil.index') }}" style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:16px;padding:10px;border-radius:10px;background:#002452;color:#fff;font-size:13px;font-weight:600;text-decoration:none;transition:opacity 0.2s;"
                       onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                        <span class="material-symbols-outlined" style="font-size:16px;">open_in_new</span> Ouvrir les Paramètres
                    </a>
                </div>
            </template>

            {{-- Step: Revenus --}}
            <template x-if="step === 'revenus'">
                <div>
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
                        <div style="width:48px;height:48px;border-radius:14px;background:#006c49;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span class="material-symbols-outlined" style="color:#fff;font-size:24px;">account_balance_wallet</span>
                        </div>
                        <div>
                            <h3 style="font-size:17px;font-weight:700;color:#1F2937;margin:0;">Revenus</h3>
                            <p style="font-size:12px;color:#6B7280;margin:2px 0 0;">Base de votre budget pour la période en cours</p>
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#ECFDF5;border:1px solid #A7F3D0;">
                            <span class="material-symbols-outlined" style="color:#006c49;font-size:20px;margin-top:2px;">payments</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Salaire fixe</strong> - À renseigner pour chaque période budgétaire (tableau de bord et page Revenus).</div>
                        </div>
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#ECFDF5;border:1px solid #A7F3D0;">
                            <span class="material-symbols-outlined" style="color:#006c49;font-size:20px;margin-top:2px;">add_circle</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Revenus variables</strong> - Primes, freelance… Le quota protège automatiquement votre épargne.</div>
                        </div>
                    </div>
                    <a href="{{ route('revenus.index') }}" style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:16px;padding:10px;border-radius:10px;background:#006c49;color:#fff;font-size:13px;font-weight:600;text-decoration:none;transition:opacity 0.2s;"
                       onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                        <span class="material-symbols-outlined" style="font-size:16px;">open_in_new</span> Configurer mes revenus
                    </a>
                </div>
            </template>

            {{-- Step: Dépenses --}}
            <template x-if="step === 'depenses'">
                <div>
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
                        <div style="width:48px;height:48px;border-radius:14px;background:#EF4444;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span class="material-symbols-outlined" style="color:#fff;font-size:24px;">shopping_cart</span>
                        </div>
                        <div>
                            <h3 style="font-size:17px;font-weight:700;color:#1F2937;margin:0;">Dépenses & Catégories</h3>
                            <p style="font-size:12px;color:#6B7280;margin:2px 0 0;">Suivez chaque franc dépensé avec précision</p>
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#FEF2F2;border:1px solid #FECACA;">
                            <span class="material-symbols-outlined" style="color:#EF4444;font-size:20px;margin-top:2px;">category</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Catégories</strong> - Alimentation, Transport, Loisirs… Personnalisez vos catégories dans Paramètres.</div>
                        </div>
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#FFFBEB;border:1px solid #FDE68A;">
                            <span class="material-symbols-outlined" style="color:#F59E0B;font-size:20px;margin-top:2px;">bolt</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Imprévues</strong> - Marquez les dépenses non planifiées pour mieux analyser vos habitudes.</div>
                        </div>
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#F0F4FF;border:1px solid #E0E7FF;">
                            <span class="material-symbols-outlined" style="color:#002452;font-size:20px;margin-top:2px;">pie_chart</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Plafonds</strong> - Définissez des limites par catégorie pour mieux contrôler vos dépenses.</div>
                        </div>
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#ECFDF5;border:1px solid #A7F3D0;">
                            <span class="material-symbols-outlined" style="color:#006c49;font-size:20px;margin-top:2px;">upload_file</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Import CSV</strong> - Import en masse ; les dates doivent rester dans la période affichée (respecte votre mois budgétaire).</div>
                        </div>
                    </div>
                    <a href="{{ route('depenses.index') }}" style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:16px;padding:10px;border-radius:10px;background:#EF4444;color:#fff;font-size:13px;font-weight:600;text-decoration:none;">
                        <span class="material-symbols-outlined" style="font-size:16px;">open_in_new</span> Ouvrir les Dépenses
                    </a>
                </div>
            </template>

            {{-- Step: Épargne --}}
            <template x-if="step === 'epargne'">
                <div>
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
                        <div style="width:48px;height:48px;border-radius:14px;background:#006c49;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span class="material-symbols-outlined" style="color:#fff;font-size:24px;">savings</span>
                        </div>
                        <div>
                            <h3 style="font-size:17px;font-weight:700;color:#1F2937;margin:0;">Épargne</h3>
                            <p style="font-size:12px;color:#6B7280;margin:2px 0 0;">Construisez votre avenir financier</p>
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#ECFDF5;border:1px solid #A7F3D0;">
                            <span class="material-symbols-outlined" style="color:#006c49;font-size:20px;margin-top:2px;">flag</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Objectifs</strong> - Créez des objectifs (maison, voyage, urgences…) et suivez la progression.</div>
                        </div>
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#ECFDF5;border:1px solid #A7F3D0;">
                            <span class="material-symbols-outlined" style="color:#006c49;font-size:20px;margin-top:2px;">trending_up</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Automatique</strong> - Un % de votre salaire est mis de côté à chaque période selon vos réglages.</div>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Step: Emprunts --}}
            <template x-if="step === 'emprunts'">
                <div>
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
                        <div style="width:48px;height:48px;border-radius:14px;background:#7C3AED;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span class="material-symbols-outlined" style="color:#fff;font-size:24px;">swap_horiz</span>
                        </div>
                        <div>
                            <h3 style="font-size:17px;font-weight:700;color:#1F2937;margin:0;">Emprunts & Prêts</h3>
                            <p style="font-size:12px;color:#6B7280;margin:2px 0 0;">Gérez vos dettes et créances</p>
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#FEF2F2;border:1px solid #FECACA;">
                            <span class="material-symbols-outlined" style="color:#DC2626;font-size:20px;margin-top:2px;">arrow_downward</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Emprunts</strong> - Ce que vous devez. Suivi des remboursements + alertes d'échéance.</div>
                        </div>
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#F5F3FF;border:1px solid #DDD6FE;">
                            <span class="material-symbols-outlined" style="color:#7C3AED;font-size:20px;margin-top:2px;">arrow_upward</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Prêts</strong> - Ce qu'on vous doit. Notification automatique à chaque remboursement reçu.</div>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Step: Automatisation --}}
            <template x-if="step === 'automatisation'">
                <div>
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
                        <div style="width:48px;height:48px;border-radius:14px;background:#002452;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span class="material-symbols-outlined" style="color:#fff;font-size:24px;">event_repeat</span>
                        </div>
                        <div>
                            <h3 style="font-size:17px;font-weight:700;color:#1F2937;margin:0;">Automatisation</h3>
                            <p style="font-size:12px;color:#6B7280;margin:2px 0 0;">Moins de saisie manuelle, plus de régularité</p>
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#F0F4FF;border:1px solid #E0E7FF;">
                            <span class="material-symbols-outlined" style="color:#002452;font-size:20px;margin-top:2px;">event_repeat</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Récurrences</strong> - Loyer, abonnements, primes fixes : générés automatiquement chaque mois au jour choisi (Profil → Récurrences).</div>
                        </div>
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#ECFDF5;border:1px solid #A7F3D0;">
                            <span class="material-symbols-outlined" style="color:#006c49;font-size:20px;margin-top:2px;">upload_file</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Import CSV</strong> - Reprenez un export Rapports ou un fichier banque (point-virgule).</div>
                        </div>
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#F5F3FF;border:1px solid #DDD6FE;">
                            <span class="material-symbols-outlined" style="color:#7C3AED;font-size:20px;margin-top:2px;">install_mobile</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Application mobile</strong> - Sur téléphone : « Ajouter à l'écran d'accueil » pour un accès rapide.</div>
                        </div>
                    </div>
                    <a href="{{ route('profil.recurrences.index') }}" style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:16px;padding:10px;border-radius:10px;background:#002452;color:#fff;font-size:13px;font-weight:600;text-decoration:none;">
                        <span class="material-symbols-outlined" style="font-size:16px;">open_in_new</span> Gérer les récurrences
                    </a>
                </div>
            </template>

            {{-- Step: Rapports --}}
            <template x-if="step === 'rapports'">
                <div>
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
                        <div style="width:48px;height:48px;border-radius:14px;background:#F59E0B;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span class="material-symbols-outlined" style="color:#fff;font-size:24px;">analytics</span>
                        </div>
                        <div>
                            <h3 style="font-size:17px;font-weight:700;color:#1F2937;margin:0;">Rapports & Alertes</h3>
                            <p style="font-size:12px;color:#6B7280;margin:2px 0 0;">Visualisez et exportez vos données</p>
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#FFFBEB;border:1px solid #FDE68A;">
                            <span class="material-symbols-outlined" style="color:#F59E0B;font-size:20px;margin-top:2px;">picture_as_pdf</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Exports PDF & CSV</strong> - Bilans mensuels, comparatifs et annuels en un clic.</div>
                        </div>
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#FFFBEB;border:1px solid #FDE68A;">
                            <span class="material-symbols-outlined" style="color:#F59E0B;font-size:20px;margin-top:2px;">notifications_active</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Alertes intelligentes</strong> - Conseils selon le contexte (budget, plafond, dette). Seules les alertes de la <strong>période en cours</strong> restent actives dans le menu.</div>
                        </div>
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#FEF2F2;border:1px solid #FECACA;">
                            <span class="material-symbols-outlined" style="color:#EF4444;font-size:20px;margin-top:2px;">history</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Fin de période</strong> - Les alertes budget du mois passé sont automatiquement archivées ; plus de mélange entre deux périodes.</div>
                        </div>
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#ECFDF5;border:1px solid #A7F3D0;">
                            <span class="material-symbols-outlined" style="color:#006c49;font-size:20px;margin-top:2px;">mail</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>E-mails</strong> - Récap hebdo et alertes critiques utilisent le même libellé de période (ex. 25 juin – 24 juil.).</div>
                        </div>
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:#F0F4FF;border:1px solid #E0E7FF;">
                            <span class="material-symbols-outlined" style="color:#002452;font-size:20px;margin-top:2px;">sync_alt</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Aller-retour CSV</strong> - Exportez ici, modifiez dans Excel, réimportez sur la page Dépenses.</div>
                        </div>
                    <a href="{{ route('alertes.index') }}" style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:16px;padding:10px;border-radius:10px;background:#F59E0B;color:#fff;font-size:13px;font-weight:600;text-decoration:none;">
                        <span class="material-symbols-outlined" style="font-size:16px;">open_in_new</span> Voir mes alertes
                    </a>
                    </div>
                </div>
            </template>

            {{-- Step: Admin --}}
            <template x-if="step === 'admin'">
                <div>
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
                        <div style="width:48px;height:48px;border-radius:14px;background:#002452;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span class="material-symbols-outlined" style="color:#fff;font-size:24px;">admin_panel_settings</span>
                        </div>
                        <div>
                            <h3 style="font-size:17px;font-weight:700;color:#1F2937;margin:0;">Administration</h3>
                            <p style="font-size:12px;color:#6B7280;margin:2px 0 0;">Vos droits et capacités d'administrateur</p>
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        @if(auth()->user()->isSuperAdmin())
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:14px;border-radius:10px;background:#F0F4FF;border:2px solid #002452;">
                            <span class="material-symbols-outlined" style="color:#002452;font-size:20px;margin-top:2px;">shield</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Super Admin</strong> - Accès total : créer/suspendre des comptes, réinitialiser les mots de passe, impersonner, journaux d'activité.</div>
                        </div>
                        @elseif(auth()->user()->isAdmin())
                        <div style="display:flex;align-items:flex-start;gap:12px;padding:14px;border-radius:10px;background:#ECFDF5;border:2px solid #006c49;">
                            <span class="material-symbols-outlined" style="color:#006c49;font-size:20px;margin-top:2px;">verified_user</span>
                            <div style="font-size:13px;color:#374151;line-height:1.5;"><strong>Admin</strong> - Gérez votre budget et rapports. La gestion des comptes est réservée au Super Admin.</div>
                        </div>
                        @endif
                    </div>
                </div>
            </template>

            {{-- Step: Finish --}}
            <template x-if="step === 'finish'">
                <div style="text-align:center;padding:16px 0;">
                    <div style="width:72px;height:72px;margin:0 auto 20px;border-radius:50%;background:linear-gradient(135deg,#006c49,#00a86b);display:flex;align-items:center;justify-content:center;box-shadow:0 8px 24px rgba(0,108,73,0.3);">
                        <span class="material-symbols-outlined" style="color:#fff;font-size:36px;">rocket_launch</span>
                    </div>
                    <h2 style="font-size:22px;font-weight:800;color:#006c49;margin-bottom:8px;font-family:'Manrope',sans-serif;">Vous êtes prêt !</h2>
                    <p style="font-size:14px;color:#6B7280;line-height:1.6;max-width:380px;margin:0 auto;">
                        1) <strong>Paramètres</strong> : jour de début de période si besoin.<br>
                        2) <strong>Revenus</strong> : salaire fixe de la période courante.<br>
                        3) Enregistrez vos <strong>dépenses</strong> — les alertes suivent.
                    </p>
                    <p style="font-size:11px;color:#9CA3AF;margin-top:16px;">Relancez ce guide avec le bouton <strong>Guide</strong> en haut du tableau de bord.</p>
                </div>
            </template>
        </div>

        {{-- Footer navigation --}}
        <div style="padding:16px 24px;border-top:1px solid #E5E7EB;display:flex;align-items:center;justify-content:space-between;background:#F8FAFC;">
            <button @click="prev()" x-show="idx > 0"
                    style="display:flex;align-items:center;gap:4px;font-size:13px;color:#6B7280;background:none;border:none;cursor:pointer;padding:8px 12px;border-radius:8px;transition:background 0.2s;"
                    onmouseover="this.style.background='#E5E7EB'" onmouseout="this.style.background='none'">
                <span class="material-symbols-outlined" style="font-size:16px;">chevron_left</span> Précédent
            </button>
            <div x-show="idx === 0" style="width:80px;"></div>

            {{-- Step dots --}}
            <div style="display:flex;align-items:center;gap:6px;">
                <template x-for="i in totalSteps" :key="i">
                    <div style="height:8px;border-radius:4px;transition:all 0.3s;"
                         :style="(i - 1) === idx ? 'width:20px;background:#002452;' : (i - 1) < idx ? 'width:8px;background:rgba(0,36,82,0.4);' : 'width:8px;background:#E5E7EB;'"></div>
                </template>
            </div>

            <template x-if="idx < lastIdx">
                <button @click="next()"
                        style="display:flex;align-items:center;gap:4px;padding:10px 20px;border-radius:10px;background:#002452;color:#fff;font-size:13px;font-weight:600;border:none;cursor:pointer;transition:opacity 0.2s;"
                        onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                    Suivant <span class="material-symbols-outlined" style="font-size:16px;">chevron_right</span>
                </button>
            </template>
            <template x-if="idx === lastIdx">
                <button @click="finish()"
                        style="display:flex;align-items:center;gap:4px;padding:10px 24px;border-radius:10px;background:#006c49;color:#fff;font-size:14px;font-weight:700;border:none;cursor:pointer;transition:opacity 0.2s;box-shadow:0 4px 12px rgba(0,108,73,0.3);"
                        onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                    <span class="material-symbols-outlined" style="font-size:18px;">check</span> C'est parti !
                </button>
            </template>
        </div>
    </div>
</div>

<script>
function onboarding() {
    const isAdmin = {{ auth()->user()->isAdmin() ? 'true' : 'false' }};
    const steps = ['welcome','params','revenus','depenses','epargne','emprunts','automatisation','rapports'];
    if (isAdmin) steps.push('admin');
    steps.push('finish');

    const autoShow = {{ auth()->user()->onboarding_done ? 'false' : 'true' }};

    return {
        show: autoShow,
        idx: 0,
        isAdmin,
        steps,
        get step() { return this.steps[this.idx]; },
        get totalSteps() { return this.steps.length; },
        get lastIdx() { return this.steps.length - 1; },
        init() {
            document.addEventListener('open-onboarding', () => {
                this.idx = 0;
                this.show = true;
            });
        },
        next() { if (this.idx < this.lastIdx) this.idx++; },
        prev() { if (this.idx > 0) this.idx--; },
        skip() { this.finish(); },
        finish() {
            this.show = false;
            if (autoShow) {
                fetch('{{ route("profil.onboarding.done") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
            }
        }
    };
}
</script>
@endauth
