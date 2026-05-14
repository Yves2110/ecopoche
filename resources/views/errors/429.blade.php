@include('errors.layout', [
    'code'     => 429,
    'icon'     => 'timer',
    'iconBg'   => 'bg-[#fef9c3]',
    'iconColor'=> 'text-[#D97706]',
    'titre'    => 'Trop de tentatives',
    'message'  => 'Vous avez effectué trop de requêtes en peu de temps. Votre accès a été temporairement suspendu. Attendez quelques minutes avant de réessayer.',
])
