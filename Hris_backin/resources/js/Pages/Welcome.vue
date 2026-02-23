<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
}>();
</script>

<template>
    <Head title="SafeXpress Logistics Management System" />
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-orange-50 welcome-page">
        <!-- Header -->
        <header class="bg-white/90 backdrop-blur-sm shadow-lg border-b border-gray-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <!-- Logo and Title -->
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl shadow-lg">
                            <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19,7H18V6A4,4 0 0,0 14,2H10A4,4 0 0,0 6,6V7H5A3,3 0 0,0 2,10V20A3,3 0 0,0 5,23H19A3,3 0 0,0 22,20V10A3,3 0 0,0 19,7M10,4H14A2,2 0 0,1 16,6V7H8V6A2,2 0 0,1 10,4M20,20A1,1 0 0,1 19,21H5A1,1 0 0,1 4,20V10A1,1 0 0,1 5,9H19A1,1 0 0,1 20,10V20M10,16.5L7.5,14L6.08,15.42L10,19.34L17.92,11.42L16.5,10L10,16.5Z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">SafeXpress</h1>
                            <p class="text-sm text-gray-600 font-medium">Logistics Management System</p>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <nav v-if="canLogin" class="flex items-center space-x-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-xl text-sm font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                        >
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-xl text-sm font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                            >
                                Login
                            </Link>
                        </template>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
                <div class="grid lg:grid-cols-2 gap-16 items-center">
                    <!-- Left Content -->
                    <div class="space-y-8">
                        <div class="space-y-6">
                            <div class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Complete Logistics Solution
                            </div>
                            <h1 class="text-6xl font-bold text-gray-900 leading-tight">
                                Streamline Your
                                <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                                    Logistics
                                </span>
                                Operations
                            </h1>
                            <p class="text-xl text-gray-700 leading-relaxed">
                                SafeXpress Management System is a comprehensive logistics platform integrating
                                <span class="font-semibold text-blue-600">Warehouse Operations</span>,
                                <span class="font-semibold text-green-600">Fleet Tracking</span>,
                                <span class="font-semibold text-orange-600">Vehicle Monitoring</span>,
                                <span class="font-semibold text-purple-600">Transportation Management</span>, and
                                <span class="font-semibold text-red-600">Human Resources</span>.
                                Transform your supply chain with intelligent automation and real-time insights.
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button class="flex items-center justify-center px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                </svg>
                                Get Started
                            </button>
                            <button class="flex items-center justify-center px-8 py-4 bg-white border-2 border-gray-300 rounded-xl text-gray-700 font-semibold hover:border-blue-400 hover:bg-blue-50 transition-all duration-300 shadow-lg hover:shadow-xl">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                                Watch Demo
                            </button>
                        </div>

                        <!-- Trust Indicators -->
                        <div class="flex items-center space-x-8 pt-8">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900">500+</div>
                                <div class="text-sm text-gray-600">Active Clients</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900">99.9%</div>
                                <div class="text-sm text-gray-600">Uptime</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900">24/7</div>
                                <div class="text-sm text-gray-600">Support</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Content - Enhanced Logo -->
                    <div class="flex justify-center lg:justify-end">
                        <div class="relative">
                            <!-- Main Logo Container -->
                            <div class="bg-gradient-to-br from-blue-500 via-purple-500 to-orange-500 rounded-3xl p-12 shadow-2xl transform rotate-3 hover:rotate-0 transition-all duration-500 hover:scale-105">
                                <div class="bg-white rounded-2xl p-8 shadow-inner">
                                    <div class="text-center space-y-4">
                                        <div class="text-5xl font-bold">
                                            <span class="text-blue-600">SAFE</span><span class="text-orange-500">X</span><span class="text-purple-600">PRESS</span>
                                            <sup class="text-lg text-gray-500">®</sup>
                                        </div>
                                        <div class="text-lg font-bold text-gray-700 tracking-wider">
                                            LOGISTICS SOLUTIONS
                                        </div>
                                        <div class="flex justify-center space-x-2 pt-2">
                                            <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                            <div class="w-2 h-2 bg-orange-500 rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
                                            <div class="w-2 h-2 bg-purple-500 rounded-full animate-pulse" style="animation-delay: 0.4s"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Floating Elements -->
                            <div class="absolute -top-6 -right-6 w-12 h-12 bg-gradient-to-r from-green-400 to-blue-500 rounded-full opacity-80 animate-bounce"></div>
                            <div class="absolute -bottom-8 -left-8 w-16 h-16 bg-gradient-to-r from-orange-400 to-red-500 rounded-full opacity-60 animate-pulse"></div>
                            <div class="absolute top-1/2 -right-12 w-8 h-8 bg-gradient-to-r from-purple-400 to-pink-500 rounded-full opacity-70 animate-ping"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Statistics Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Trusted by Industry Leaders</h2>
                    <p class="text-xl text-gray-600">Delivering excellence in logistics management worldwide</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl">
                        <div class="text-4xl font-bold text-blue-600 mb-2">1M+</div>
                        <div class="text-gray-700 font-medium">Shipments Tracked</div>
                    </div>
                    <div class="text-center p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-2xl">
                        <div class="text-4xl font-bold text-green-600 mb-2">50K+</div>
                        <div class="text-gray-700 font-medium">Vehicles Monitored</div>
                    </div>
                    <div class="text-center p-6 bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl">
                        <div class="text-4xl font-bold text-orange-600 mb-2">200+</div>
                        <div class="text-gray-700 font-medium">Warehouses</div>
                    </div>
                    <div class="text-center p-6 bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl">
                        <div class="text-4xl font-bold text-purple-600 mb-2">95%</div>
                        <div class="text-gray-700 font-medium">Efficiency Gain</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 bg-gradient-to-br from-gray-50 to-blue-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Complete Logistics Ecosystem</h2>
                    <p class="text-xl text-gray-600">Integrated modules for seamless operations management</p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Warehouse Management -->
                    <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100">
                        <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12,2L2,7V10C2,16.5 6.5,21.5 12,23C17.5,21.5 22,16.5 22,10V7L12,2M11,7H13V9H15V11H13V13H11V11H9V9H11V7Z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Warehouse Management</h3>
                        <p class="text-gray-600 mb-6">Advanced inventory control, real-time stock monitoring, and automated warehouse operations with KPI tracking.</p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-center"><span class="w-2 h-2 bg-blue-500 rounded-full mr-3"></span>Real-time Inventory Tracking</li>
                            <li class="flex items-center"><span class="w-2 h-2 bg-blue-500 rounded-full mr-3"></span>Automated Stock Alerts</li>
                            <li class="flex items-center"><span class="w-2 h-2 bg-blue-500 rounded-full mr-3"></span>Performance Analytics</li>
                        </ul>
                    </div>

                    <!-- Fleet Tracking -->
                    <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100">
                        <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-green-600 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18,18.5A1.5,1.5 0 0,1 16.5,17A1.5,1.5 0 0,1 18,15.5A1.5,1.5 0 0,1 19.5,17A1.5,1.5 0 0,1 18,18.5M19.5,9.5L21.46,12H17V9.5M6,18.5A1.5,1.5 0 0,1 4.5,17A1.5,1.5 0 0,1 6,15.5A1.5,1.5 0 0,1 7.5,17A1.5,1.5 0 0,1 6,18.5M20,8H17V4H3C1.89,4 1,4.89 1,6V17H3A3,3 0 0,0 6,20A3,3 0 0,0 9,17H15A3,3 0 0,0 18,20A3,3 0 0,0 21,17H23V12L20,8Z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Fleet Tracking System</h3>
                        <p class="text-gray-600 mb-6">GPS-enabled vehicle tracking, route optimization, and comprehensive fleet management solutions.</p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-center"><span class="w-2 h-2 bg-green-500 rounded-full mr-3"></span>Live GPS Tracking</li>
                            <li class="flex items-center"><span class="w-2 h-2 bg-green-500 rounded-full mr-3"></span>Route Optimization</li>
                            <li class="flex items-center"><span class="w-2 h-2 bg-green-500 rounded-full mr-3"></span>Fuel Management</li>
                        </ul>
                    </div>

                    <!-- Transportation Management -->
                    <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100">
                        <div class="w-16 h-16 bg-gradient-to-r from-orange-500 to-orange-600 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8Z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Transportation Management</h3>
                        <p class="text-gray-600 mb-6">End-to-end shipment management, delivery scheduling, and customer communication systems.</p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-center"><span class="w-2 h-2 bg-orange-500 rounded-full mr-3"></span>Shipment Scheduling</li>
                            <li class="flex items-center"><span class="w-2 h-2 bg-orange-500 rounded-full mr-3"></span>Delivery Tracking</li>
                            <li class="flex items-center"><span class="w-2 h-2 bg-orange-500 rounded-full mr-3"></span>Customer Notifications</li>
                        </ul>
                    </div>

                    <!-- Financial Management -->
                    <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100">
                        <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M7,15H9C9,16.08 10.37,17 12,17C13.63,17 15,16.08 15,15C15,13.9 13.96,13.5 11.76,12.97C9.64,12.44 7,11.78 7,9C7,7.21 8.47,5.69 10.5,5.18V3H13.5V5.18C15.53,5.69 17,7.21 17,9H15C15,7.92 13.63,7 12,7C10.37,7 9,7.92 9,9C9,10.1 10.04,10.5 12.24,11.03C14.36,11.56 17,12.22 17,15C17,16.79 15.53,18.31 13.5,18.82V21H10.5V18.82C8.47,18.31 7,16.79 7,15Z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Financial Management</h3>
                        <p class="text-gray-600 mb-6">Comprehensive financial tracking, expense management, and automated billing systems.</p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-center"><span class="w-2 h-2 bg-purple-500 rounded-full mr-3"></span>Expense Tracking</li>
                            <li class="flex items-center"><span class="w-2 h-2 bg-purple-500 rounded-full mr-3"></span>Automated Billing</li>
                            <li class="flex items-center"><span class="w-2 h-2 bg-purple-500 rounded-full mr-3"></span>Financial Reports</li>
                        </ul>
                    </div>

                    <!-- Human Resources -->
                    <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100">
                        <div class="w-16 h-16 bg-gradient-to-r from-red-500 to-red-600 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M16,4C18.11,4 20,5.89 20,8A4,4 0 0,1 16,12A4,4 0 0,1 12,8A4,4 0 0,1 16,4M16,6A2,2 0 0,0 14,8A2,2 0 0,0 16,10A2,2 0 0,0 18,8A2,2 0 0,0 16,6M16,13C18.67,13 22,14.33 22,17V20H10V17C10,14.33 13.33,13 16,13M8,12A4,4 0 0,1 4,8A4,4 0 0,1 8,4A4,4 0 0,1 12,8A4,4 0 0,1 8,12M8,13C5.33,13 0,14.33 0,17V20H8V17C8,15.9 8.6,14.93 9.5,14.21C8.86,14.08 8.19,14 8,14Z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Human Resources</h3>
                        <p class="text-gray-600 mb-6">Complete HR management including payroll, attendance, performance tracking, and employee management.</p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-center"><span class="w-2 h-2 bg-red-500 rounded-full mr-3"></span>Employee Management</li>
                            <li class="flex items-center"><span class="w-2 h-2 bg-red-500 rounded-full mr-3"></span>Payroll Processing</li>
                            <li class="flex items-center"><span class="w-2 h-2 bg-red-500 rounded-full mr-3"></span>Performance Tracking</li>
                        </ul>
                    </div>

                    <!-- Analytics & Reporting -->
                    <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100">
                        <div class="w-16 h-16 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22,21H2V3H4V19H6V10H10V19H12V6H16V19H18V14H22V21Z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Analytics & Reporting</h3>
                        <p class="text-gray-600 mb-6">Advanced business intelligence, custom dashboards, and comprehensive reporting tools.</p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-center"><span class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></span>Custom Dashboards</li>
                            <li class="flex items-center"><span class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></span>Business Intelligence</li>
                            <li class="flex items-center"><span class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></span>Data Visualization</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="py-20 bg-gradient-to-r from-blue-600 to-purple-600">
            <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
                <h2 class="text-4xl font-bold text-white mb-6">Ready to Transform Your Logistics?</h2>
                <p class="text-xl text-blue-100 mb-8">Join thousands of companies already using SafeXpress to optimize their operations</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button class="px-8 py-4 bg-white text-blue-600 rounded-xl font-semibold hover:bg-gray-100 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        Start Free Trial
                    </button>
                    <button class="px-8 py-4 bg-transparent border-2 border-white text-white rounded-xl font-semibold hover:bg-white hover:text-blue-600 transition-all duration-300">
                        Schedule Demo
                    </button>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-4 gap-8">
                    <div class="col-span-2">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19,7H18V6A4,4 0 0,0 14,2H10A4,4 0 0,0 6,6V7H5A3,3 0 0,0 2,10V20A3,3 0 0,0 5,23H19A3,3 0 0,0 22,20V10A3,3 0 0,0 19,7M10,4H14A2,2 0 0,1 16,6V7H8V6A2,2 0 0,1 10,4M20,20A1,1 0 0,1 19,21H5A1,1 0 0,1 4,20V10A1,1 0 0,1 5,9H19A1,1 0 0,1 20,10V20M10,16.5L7.5,14L6.08,15.42L10,19.34L17.92,11.42L16.5,10L10,16.5Z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold">SafeXpress</h3>
                                <p class="text-gray-400">Logistics Management System</p>
                            </div>
                        </div>
                        <p class="text-gray-400 mb-4">Empowering logistics companies with comprehensive management solutions for the modern supply chain.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Solutions</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li>Warehouse Management</li>
                            <li>Fleet Tracking</li>
                            <li>Transportation</li>
                            <li>Financial Management</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Support</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li>Documentation</li>
                            <li>Help Center</li>
                            <li>Contact Support</li>
                            <li>System Status</li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                    <p>&copy; 2024 SafeXpress Logistics Management System. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Force light mode for welcome page - override dark mode styles */
[data-theme="dark"] .welcome-page {
  color-scheme: light !important;
}

[data-theme="dark"] .welcome-page,
[data-theme="dark"] .welcome-page * {
  background-color: initial !important;
  color: initial !important;
  border-color: initial !important;
}

[data-theme="dark"] .welcome-page {
  background: #ffffff !important;
}

[data-theme="dark"] .welcome-page header {
  background: rgba(255, 255, 255, 0.9) !important;
  backdrop-filter: blur(8px) !important;
  border-color: rgb(229 231 235) !important;
}

[data-theme="dark"] .welcome-page .hero-section {
  background: transparent !important;
  color: #1a202c !important;
}

[data-theme="dark"] .welcome-page .hero-section h1 {
  color: #1f2937 !important;
}

[data-theme="dark"] .welcome-page .hero-section p {
  color: #374151 !important;
}

[data-theme="dark"] .welcome-page .stats-section {
  background: #f9fafb !important;
}

[data-theme="dark"] .welcome-page .stats-card {
  background: white !important;
  color: #1f2937 !important;
  border: 1px solid #e5e7eb !important;
}

[data-theme="dark"] .welcome-page .stats-card h3 {
  color: #1d4ed8 !important;
}

[data-theme="dark"] .welcome-page .stats-card p {
  color: #6b7280 !important;
}

[data-theme="dark"] .welcome-page .modules-section {
  background: white !important;
}

[data-theme="dark"] .welcome-page .modules-section h2 {
  color: #1f2937 !important;
}

[data-theme="dark"] .welcome-page .module-card {
  background: white !important;
  color: #1f2937 !important;
  border: 1px solid #e5e7eb !important;
}

[data-theme="dark"] .welcome-page .module-card:hover {
  background: #f9fafb !important;
  border-color: #d1d5db !important;
}

[data-theme="dark"] .welcome-page .module-card h3 {
  color: #1d4ed8 !important;
}

[data-theme="dark"] .welcome-page .module-card p {
  color: #6b7280 !important;
}

[data-theme="dark"] .welcome-page .module-card .feature-item {
  color: #9ca3af !important;
}

[data-theme="dark"] .welcome-page .services-section {
  background: #f9fafb !important;
}

[data-theme="dark"] .welcome-page .services-section h2 {
  color: #1f2937 !important;
}

[data-theme="dark"] .welcome-page .service-card {
  background: white !important;
  color: #1f2937 !important;
  border: 1px solid #e5e7eb !important;
}

[data-theme="dark"] .welcome-page .service-card h3 {
  color: #1d4ed8 !important;
}

[data-theme="dark"] .welcome-page .service-card p {
  color: #6b7280 !important;
}

[data-theme="dark"] .welcome-page footer {
  background: #1f2937 !important;
  color: #d1d5db !important;
}

[data-theme="dark"] .welcome-page footer h3 {
  color: white !important;
}

[data-theme="dark"] .welcome-page footer p,
[data-theme="dark"] .welcome-page footer a {
  color: #9ca3af !important;
}

[data-theme="dark"] .welcome-page footer a:hover {
  color: #d1d5db !important;
}
</style>
