@include('errors.layout', [
    'code'     => 403,
    'icon'     => 'lock',
    'iconBg'   => 'bg-[#EEF2FF]',
    'iconColor'=> 'text-[#4f46e5]',
    'titre'    => 'Accès refusé',
    'message'  => 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page. Si vous pensez qu\'il s\'agit d\'une erreur, contactez l\'administrateur.',
])
