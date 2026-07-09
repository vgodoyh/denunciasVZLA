<x-error-page
    code="401"
    title="Necesitás iniciar sesión"
    message="Tu sesión no está activa o expiró. Iniciá sesión para continuar."
    icon="fa-solid fa-lock"
    variant="error"
    cta-text="Iniciar sesión"
    :cta-url="route('login')"
/>