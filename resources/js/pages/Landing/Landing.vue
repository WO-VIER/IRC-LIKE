<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { login, register } from '@/routes'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import {
    MessageCircle,
    Zap,
    Shield,
    Users,
    Check,
    ArrowRight,
    Clock,
    Lock,
    Globe,
    Sparkles
} from 'lucide-vue-next'
</script>

<template>

    <Head title="IRC-LIKE - Messagerie Temps Réel pour Équipes" />

    <div
        class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-900 dark:via-blue-900/20 dark:to-purple-900/20">
        <!-- Header / Navigation -->
        <header
            class="sticky top-0 z-50 bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg border-b border-gray-200 dark:border-gray-700">
            <nav class="container mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <!-- Logo -->
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                            <MessageCircle class="h-6 w-6 text-white" />
                        </div>
                        <span
                            class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                            IRC-LIKE
                        </span>
                    </div>

                    <!-- Navigation Links -->
                    <div class="flex items-center space-x-4">
                        <Link :href="login()"
                            class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                        Se connecter
                        </Link>
                        <Link :href="register()">
                        <Button
                            class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 shadow-lg">
                            Commencer gratuitement
                            <ArrowRight class="ml-2 h-4 w-4" />
                        </Button>
                        </Link>
                    </div>
                </div>
            </nav>
        </header>

        <!-- Hero Section -->
        <section class="container mx-auto px-6 py-20 lg:py-32">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Left Column - Text -->
                <div class="space-y-8">
                    <Badge class="bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 border-0">
                        <Sparkles class="h-3 w-3 mr-1" />
                        Messagerie Temps Réel B2B
                    </Badge>

                    <h1 class="text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white leading-tight">
                        Collaborez en
                        <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                            temps réel
                        </span>
                        avec votre équipe
                    </h1>

                    <p class="text-xl text-gray-600 dark:text-gray-300 leading-relaxed">
                        Une solution de messagerie professionnelle conçue pour les équipes qui veulent communiquer
                        efficacement.
                        Messages instantanés, conversations de groupe et notifications en temps réel.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <Link :href="register()">
                        <Button size="lg"
                            class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-lg px-8 shadow-xl">
                            Essayer maintenant
                            <ArrowRight class="ml-2 h-5 w-5" />
                        </Button>
                        </Link>
                        <Button size="lg" variant="outline" class="text-lg px-8">
                            Voir la démo
                        </Button>
                    </div>

                    <!-- Stats -->
                    <div class="flex items-center gap-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                        <div>
                            <div class="text-3xl font-bold text-gray-900 dark:text-white">100%</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Temps réel</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-gray-900 dark:text-white">Sécurisé</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Chiffré de bout en bout</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-gray-900 dark:text-white">Gratuit</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Pour commencer</div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Visual -->
                <div class="relative">
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-600 rounded-3xl blur-3xl opacity-20">
                    </div>
                    <div
                        class="relative bg-white dark:bg-gray-800 rounded-3xl shadow-2xl p-8 border border-gray-200 dark:border-gray-700">
                        <!-- Mock Chat Interface -->
                        <div class="space-y-4">
                            <div
                                class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="w-10 h-10 bg-gradient-to-r from-green-400 to-blue-500 rounded-xl flex items-center justify-center">
                                        <Users class="h-5 w-5 text-white" />
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-white">Équipe Dev</div>
                                        <div class="text-sm text-gray-500">5 membres en ligne</div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-start space-x-3">
                                    <div
                                        class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex-shrink-0">
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white mb-1">Alice</div>
                                        <div
                                            class="bg-gray-100 dark:bg-gray-700 rounded-2xl px-4 py-2 text-sm text-gray-800 dark:text-gray-200">
                                            Le nouveau projet est prêt ! 🎉
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-start space-x-3">
                                    <div
                                        class="w-8 h-8 bg-gradient-to-r from-green-400 to-blue-500 rounded-lg flex-shrink-0">
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white mb-1">Bob</div>
                                        <div
                                            class="bg-gray-100 dark:bg-gray-700 rounded-2xl px-4 py-2 text-sm text-gray-800 dark:text-gray-200">
                                            Super ! On déploie quand ?
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-start space-x-3 justify-end">
                                    <div class="flex-1 flex justify-end">
                                        <div
                                            class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl px-4 py-2 text-sm text-white">
                                            Dès maintenant ! ⚡
                                        </div>
                                    </div>
                                    <div
                                        class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex-shrink-0">
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                                    <Clock class="h-4 w-4" />
                                    <span>Messages en temps réel</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="container mx-auto px-6 py-20 lg:py-32">
            <div class="text-center mb-16">
                <Badge class="mb-4 bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 border-0">
                    Fonctionnalités
                </Badge>
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-4">
                    Tout ce dont votre équipe a besoin
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                    Une plateforme complète pour communiquer, collaborer et réussir ensemble
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Feature 1 -->
                <Card class="border-2 hover:border-blue-500 transition-all duration-300 hover:shadow-xl">
                    <CardHeader>
                        <div
                            class="w-12 h-12 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl flex items-center justify-center mb-4">
                            <Zap class="h-6 w-6 text-white" />
                        </div>
                        <CardTitle>Temps Réel</CardTitle>
                        <CardDescription>
                            Messages instantanés sans rechargement. Communication fluide et réactive.
                        </CardDescription>
                    </CardHeader>
                </Card>

                <!-- Feature 2 -->
                <Card class="border-2 hover:border-purple-500 transition-all duration-300 hover:shadow-xl">
                    <CardHeader>
                        <div
                            class="w-12 h-12 bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl flex items-center justify-center mb-4">
                            <Shield class="h-6 w-6 text-white" />
                        </div>
                        <CardTitle>Sécurisé</CardTitle>
                        <CardDescription>
                            Vos données sont protégées avec un chiffrement de bout en bout.
                        </CardDescription>
                    </CardHeader>
                </Card>

                <!-- Feature 3 -->
                <Card class="border-2 hover:border-green-500 transition-all duration-300 hover:shadow-xl">
                    <CardHeader>
                        <div
                            class="w-12 h-12 bg-gradient-to-r from-green-600 to-blue-600 rounded-xl flex items-center justify-center mb-4">
                            <Users class="h-6 w-6 text-white" />
                        </div>
                        <CardTitle>Collaboratif</CardTitle>
                        <CardDescription>
                            Conversations privées et groupes pour toute votre équipe.
                        </CardDescription>
                    </CardHeader>
                </Card>

                <!-- Feature 4 -->
                <Card class="border-2 hover:border-orange-500 transition-all duration-300 hover:shadow-xl">
                    <CardHeader>
                        <div
                            class="w-12 h-12 bg-gradient-to-r from-orange-600 to-red-600 rounded-xl flex items-center justify-center mb-4">
                            <Globe class="h-6 w-6 text-white" />
                        </div>
                        <CardTitle>Accessible</CardTitle>
                        <CardDescription>
                            Accessible partout, depuis n'importe quel appareil, à tout moment.
                        </CardDescription>
                    </CardHeader>
                </Card>
            </div>
        </section>

        <!-- Pricing Section -->
        <section class="container mx-auto px-6 py-20 lg:py-32">
            <div class="text-center mb-16">
                <Badge class="mb-4 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 border-0">
                    Tarification
                </Badge>
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-4">
                    Des plans adaptés à vos besoins
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-300">
                    Commencez gratuitement, évoluez quand vous êtes prêt
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                <!-- Plan Free -->
                <Card class="border-2 hover:shadow-xl transition-all duration-300">
                    <CardHeader>
                        <div class="flex items-center justify-between mb-4">
                            <Badge variant="secondary">Gratuit</Badge>
                            <div class="text-right">
                                <div class="text-4xl font-bold text-gray-900 dark:text-white">0€</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">par mois</div>
                            </div>
                        </div>
                        <CardTitle class="text-2xl">Plan Free</CardTitle>
                        <CardDescription>
                            Parfait pour les petites équipes qui démarrent
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <ul class="space-y-3">
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <Check class="h-5 w-5 text-green-500 mr-3 flex-shrink-0" />
                                <span><strong>1 000 messages</strong> par mois</span>
                            </li>
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <Check class="h-5 w-5 text-green-500 mr-3 flex-shrink-0" />
                                <span><strong>5 membres</strong> maximum</span>
                            </li>
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <Check class="h-5 w-5 text-green-500 mr-3 flex-shrink-0" />
                                <span><strong>3 conversations</strong> de groupe</span>
                            </li>
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <Check class="h-5 w-5 text-green-500 mr-3 flex-shrink-0" />
                                <span>Pièces jointes <strong>jusqu'à 5 MB</strong></span>
                            </li>
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <Check class="h-5 w-5 text-green-500 mr-3 flex-shrink-0" />
                                <span>Historique <strong>30 jours</strong></span>
                            </li>
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <Check class="h-5 w-5 text-green-500 mr-3 flex-shrink-0" />
                                <span>Support communautaire</span>
                            </li>
                        </ul>

                        <Link :href="register()" class="block">
                        <Button variant="outline" size="lg" class="w-full">
                            Commencer gratuitement
                        </Button>
                        </Link>
                    </CardContent>
                </Card>

                <!-- Plan Pro -->
                <Card
                    class="border-2 border-blue-500 hover:shadow-2xl transition-all duration-300 relative overflow-hidden">
                    <div
                        class="absolute top-0 right-0 bg-gradient-to-r from-blue-600 to-purple-600 text-white px-4 py-1 text-sm font-semibold rounded-bl-lg">
                        Populaire
                    </div>
                    <CardHeader>
                        <div class="flex items-center justify-between mb-4">
                            <Badge class="bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 border-0">
                                Pro
                            </Badge>
                            <div class="text-right">
                                <div
                                    class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                                    29€
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">par mois</div>
                            </div>
                        </div>
                        <CardTitle class="text-2xl">Plan Pro</CardTitle>
                        <CardDescription>
                            Pour les équipes professionnelles en croissance
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <ul class="space-y-3">
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <Check class="h-5 w-5 text-blue-500 mr-3 flex-shrink-0" />
                                <span><strong>Messages illimités</strong></span>
                            </li>
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <Check class="h-5 w-5 text-blue-500 mr-3 flex-shrink-0" />
                                <span><strong>Membres illimités</strong></span>
                            </li>
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <Check class="h-5 w-5 text-blue-500 mr-3 flex-shrink-0" />
                                <span><strong>Groupes illimités</strong></span>
                            </li>
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <Check class="h-5 w-5 text-blue-500 mr-3 flex-shrink-0" />
                                <span>Pièces jointes <strong>jusqu'à 100 MB</strong></span>
                            </li>
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <Check class="h-5 w-5 text-blue-500 mr-3 flex-shrink-0" />
                                <span>Historique <strong>illimité</strong></span>
                            </li>
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <Check class="h-5 w-5 text-blue-500 mr-3 flex-shrink-0" />
                                <span><strong>Support prioritaire 24/7</strong></span>
                            </li>
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <Check class="h-5 w-5 text-blue-500 mr-3 flex-shrink-0" />
                                <span><strong>Statistiques avancées</strong></span>
                            </li>
                            <li class="flex items-center text-gray-700 dark:text-gray-300">
                                <Check class="h-5 w-5 text-blue-500 mr-3 flex-shrink-0" />
                                <span><strong>Intégrations tierces</strong></span>
                            </li>
                        </ul>

                        <Link :href="register()" class="block">
                        <Button size="lg"
                            class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 shadow-lg">
                            Essayer Pro gratuitement
                            <ArrowRight class="ml-2 h-5 w-5" />
                        </Button>
                        </Link>
                    </CardContent>
                </Card>
            </div>
        </section>

        <!-- CTA Final Section -->
        <section class="container mx-auto px-6 py-20 lg:py-32">
            <div class="relative">
                <div
                    class="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-600 rounded-3xl blur-3xl opacity-20">
                </div>
                <Card class="relative border-0 bg-gradient-to-r from-blue-600 to-purple-600 text-white overflow-hidden">
                    <CardContent class="p-12 lg:p-20">
                        <div class="max-w-3xl mx-auto text-center space-y-8">
                            <h2 class="text-4xl lg:text-5xl font-bold">
                                Prêt à transformer votre communication d'équipe ?
                            </h2>
                            <p class="text-xl text-blue-100">
                                Rejoignez des centaines d'équipes qui utilisent déjà IRC-LIKE pour collaborer
                                efficacement.
                                Commencez gratuitement, aucune carte bancaire requise.
                            </p>
                            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                <Link :href="register()">
                                <Button size="lg" variant="secondary"
                                    class="bg-white text-blue-600 hover:bg-gray-100 text-lg px-8 shadow-xl">
                                    Créer mon compte
                                    <ArrowRight class="ml-2 h-5 w-5" />
                                </Button>
                                </Link>
                                <Link :href="login()">
                                <Button size="lg" variant="outline"
                                    class="border-2 border-white text-white hover:bg-white/10 text-lg px-8">
                                    Se connecter
                                </Button>
                                </Link>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            <div class="container mx-auto px-6 py-12">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div class="flex items-center space-x-3 mb-4 md:mb-0">
                        <div
                            class="w-8 h-8 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                            <MessageCircle class="h-5 w-5 text-white" />
                        </div>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">IRC-LIKE</span>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        © 2025 IRC-LIKE. Tous droits réservés.
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
