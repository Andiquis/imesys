<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing Page - Empresa Tech</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900">

    <!-- Header -->
    <header class="bg-white shadow-md">
        <div class="container mx-auto flex justify-between items-center p-4">
            <h1 class="text-2xl font-bold text-blue-600">Empresa Tech</h1>
            <nav>
                <ul class="flex space-x-4">
                    <li><a href="#servicios" class="hover:text-blue-500">Servicios</a></li>
                    <li><a href="#contacto" class="hover:text-blue-500">Contacto</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="bg-blue-600 text-white text-center py-20">
        <h2 class="text-4xl font-bold">Innovación para tu negocio</h2>
        <p class="mt-4 text-lg">Soluciones digitales adaptadas a tu empresa</p>
        <a href="#contacto" class="mt-6 inline-block bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold">Contáctanos</a>
    </section>

    <!-- Servicios -->
    <section id="servicios" class="py-16 container mx-auto">
        <h2 class="text-3xl font-bold text-center mb-8">Nuestros Servicios</h2>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white p-6 shadow-md rounded-lg">
                <h3 class="text-xl font-semibold">Desarrollo de Software</h3>
                <p class="mt-2">Creamos soluciones digitales personalizadas para tu empresa.</p>
            </div>
            <div class="bg-white p-6 shadow-md rounded-lg">
                <h3 class="text-xl font-semibold">Transformación Digital</h3>
                <p class="mt-2">Modernizamos tu negocio con las últimas tecnologías.</p>
            </div>
            <div class="bg-white p-6 shadow-md rounded-lg">
                <h3 class="text-xl font-semibold">Consultoría Tecnológica</h3>
                <p class="mt-2">Te asesoramos para mejorar tu estrategia digital.</p>
            </div>
        </div>
    </section>

    <!-- Contacto -->
    <section id="contacto" class="bg-gray-200 py-16">
        <div class="container mx-auto text-center">
            <h2 class="text-3xl font-bold">Contáctanos</h2>
            <p class="mt-2">Déjanos tu mensaje y te responderemos pronto.</p>
            <form action="contacto.php" method="POST" class="mt-6 max-w-md mx-auto">
                <input type="text" name="nombre" placeholder="Tu Nombre" class="w-full p-3 mb-4 rounded-md">
                <input type="email" name="email" placeholder="Tu Correo" class="w-full p-3 mb-4 rounded-md">
                <textarea name="mensaje" placeholder="Tu Mensaje" class="w-full p-3 mb-4 rounded-md"></textarea>
                <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold">Enviar</button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white text-center py-4">
        <p>&copy; 2025 Empresa Tech. Todos los derechos reservados.</p>
    </footer>

</body>
</html>
