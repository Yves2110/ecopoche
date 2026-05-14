@include('errors.layout', [
    'code'     => 404,
    'icon'     => 'search_off',
    'iconBg'   => 'bg-[#F3F4F6]',
    'iconColor'=> 'text-[#6B7280]',
    'titre'    => 'Page introuvable',
    'message'  => 'La page que vous recherchez n\'existe pas ou a été déplacée. Vérifiez l\'URL ou revenez à l\'accueil.',
])
