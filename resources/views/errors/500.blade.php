@include('errors.layout', [
    'code'     => 500,
    'icon'     => 'error',
    'iconBg'   => 'bg-[#fee2e2]',
    'iconColor'=> 'text-[#DC2626]',
    'titre'    => 'Erreur serveur',
    'message'  => 'Une erreur inattendue s\'est produite. L\'équipe technique a été notifiée. Veuillez réessayer dans quelques instants.',
])
