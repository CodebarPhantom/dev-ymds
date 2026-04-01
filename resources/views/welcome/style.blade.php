<script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif'],
                        'quicksand': ['Quicksand', 'sans-serif'],
                    },
                    colors: {
                        primary: '#3b82f6',
                        secondary: '#10b981',
                        accent: '#8b5cf6',
                        dark: '#1e293b',
                        light: '#f8fafc',
                        rumioBlue: '#3b82f6',
                        rumioGreen: '#10b981',
                    },
                    animation: {
                        'float-slow': 'float 8s ease-in-out infinite',
                        'bounce-gentle': 'bounceGentle 4s ease-in-out infinite',
                        'wiggle': 'wiggle 2s ease-in-out infinite',
                        'pulse-subtle': 'pulseSubtle 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'fade-up': 'fadeUp 0.5s ease-out',
                        'scale-in': 'scaleIn 0.3s ease-out',
                        'slide-up': 'slideUp 0.4s ease-out',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-15px)' },
                        },
                        bounceGentle: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-8px)' },
                        },
                        wiggle: {
                            '0%, 100%': { transform: 'rotate(-3deg)' },
                            '50%': { transform: 'rotate(3deg)' },
                        },
                        pulseSubtle: {
                            '0%, 100%': { opacity: '1' },
                            '50%': { opacity: '0.8' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        fadeUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        scaleIn: {
                            '0%': { transform: 'scale(0.9)', opacity: '0' },
                            '100%': { transform: 'scale(1)', opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(30px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #fefefe;
            color: #1e293b;
            overflow-x: hidden;
            position: relative;
        }

        /* Custom Background Pattern */
        .bg-pattern {
            background-color: #f8fafc;
            background-image:
                radial-gradient(circle at 20% 50%, rgba(59, 130, 246, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(16, 185, 129, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(139, 92, 246, 0.05) 0%, transparent 50%);
        }

        /* Colorful Shapes */
        .shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(40px);
            opacity: 0.15;
            z-index: -1;
        }

        .shape-1 {
            width: 300px;
            height: 300px;
            background: #3b82f6;
            top: 10%;
            right: 5%;
            animation: float-slow 8s infinite ease-in-out;
        }

        .shape-2 {
            width: 250px;
            height: 250px;
            background: #10b981;
            bottom: 15%;
            left: 5%;
            animation: float-slow 8s infinite ease-in-out;
            animation-delay: 2s;
        }

        .shape-3 {
            width: 200px;
            height: 200px;
            background: #8b5cf6;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: float-slow 8s infinite ease-in-out;
            animation-delay: 4s;
        }

        /* Card Styles */
        .card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #10b981);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        /* Icon Box */
        .icon-container {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            background: linear-gradient(135deg, #3b82f6, #10b981);
            color: white;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
            transition: all 0.3s ease;
        }

        .card:hover .icon-container {
            transform: scale(1.1) rotate(5deg);
        }

        /* Button Styles */
        .btn {
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-family: 'Quicksand', sans-serif;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #10b981);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #25d366, #128c7e);
            color: white;
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.4);
        }

        .btn-outline {
            background: transparent;
            color: #374151;
            border: 2px solid #d1d5db;
        }

        .btn-outline:hover {
            background: #f9fafb;
            border-color: #9ca3af;
            transform: translateY(-1px);
        }

        /* Tab Styles */
        .tab-container {
            display: flex;
            gap: 8px;
            background: #f8fafc;
            padding: 6px;
            border-radius: 16px;
            position: relative;
        }

        .tab-button {
            flex: 1;
            padding: 12px 20px;
            border: none;
            background: transparent;
            color: #64748b;
            font-weight: 500;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
            font-family: 'Quicksand', sans-serif;
        }

        .tab-button.active {
            color: white;
        }

        .tab-indicator {
            position: absolute;
            top: 6px;
            left: 6px;
            height: calc(100% - 12px);
            background: linear-gradient(135deg, #3b82f6, #10b981);
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        /* Bank Card Styles */
        .bank-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .bank-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #10b981);
        }

        .bank-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        /* Contact Chip */
        .contact-chip {
            background: white;
            border-radius: 16px;
            padding: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
        }

        .contact-chip:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            border-color: #3b82f6;
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: white;
            border-radius: 50px;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            z-index: 9999;
            border: 1px solid #f1f5f9;
        }

        .toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        /* Header Animation */
        .logo-container {
            animation: bounce-gentle 4s ease-in-out infinite;
        }

        /* Decorative Elements */
        .deco-circle {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #10b981);
            opacity: 0.1;
            z-index: -1;
        }

        .deco-circle-1 {
            width: 120px;
            height: 120px;
            top: -30px;
            right: -30px;
        }

        .deco-circle-2 {
            width: 80px;
            height: 80px;
            bottom: -20px;
            left: -20px;
        }

        /* Copy Button */
        .copy-btn {
            transition: all 0.2s ease;
        }

        .copy-btn:active {
            transform: scale(0.95);
        }

        /* Gradient Text */
        .gradient-text {
            background: linear-gradient(135deg, #3b82f6 0%, #10b981 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Mobile select dropdown fix */
        select {
            position: relative;
            z-index: 10;
        }

        /* Ensure form elements don't interfere with dropdowns */
        .card {
            position: relative;
            z-index: 1;
        }

        /* Mobile specific fixes */
        @media (max-width: 640px) {
            .grid-cols-2 {
                grid-template-columns: 1fr;
            }
            
            /* Fix for mobile select dropdowns */
            select {
                -webkit-appearance: menulist;
                -moz-appearance: menulist;
                appearance: menulist;
                background-color: white;
                position: static !important;
                transform: none !important;
                border-radius: 8px !important;
            }
            
            /* Remove any transforms that might interfere */
            .card {
                transform: none !important;
            }
            
            /* Ensure body doesn't have overflow issues */
            body {
                overflow-x: hidden;
                position: relative;
            }
            
            /* Remove any fixed positioning that might interfere */
            .shape {
                position: absolute;
            }
        }
    </style>