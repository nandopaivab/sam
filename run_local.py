#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os
import sys
import shutil
import subprocess
import webbrowser
import time

def check_php():
    if shutil.which("php") is not None:
        return shutil.which("php")
    # Fallback to Laravel Herd default installation path
    herd_php = os.path.expanduser("~/Library/Application Support/Herd/bin/php")
    if os.path.exists(herd_php):
        return herd_php
    return None

def main():
    print("==================================================")
    print("    TrendHunter Brasil - Assistente Local         ")
    print("==================================================")
    
    php_path = check_php()
    if php_path:
        print(f"\n[OK] PHP encontrado em: {php_path}")
        
        # Start server
        print("\nIniciando servidor local na porta 8085...")
        print("Pressione Ctrl+C para encerrar o servidor.")
        
        # Open browser in a separate thread
        def open_browser():
            time.sleep(1.5)
            url = "http://localhost:8000/login.php"
            print(f"\nAbrindo navegador em: {url}")
            webbrowser.open(url)
            
        import threading
        threading.Thread(target=open_browser, daemon=True).start()
        
        # Start PHP Server
        try:
            subprocess.run([php_path, "-S", "localhost:8000", "-t", ".", "-d", "opcache.enable=0"], cwd=os.path.dirname(os.path.abspath(__file__)))
        except KeyboardInterrupt:
            print("\nServidor encerrado.")
    else:
        print("\n[!] PHP não foi encontrado no seu macOS.")
        print("Para rodar este projeto em PHP 8.4 localmente, você precisa instalar o PHP.")
        print("\nEscolha uma das opções abaixo para instalar em 1 minuto:")
        print("--------------------------------------------------")
        print("Opção 1: Laravel Herd (RECOMENDADO - Fácil & Visual)")
        print("1. Baixe o instalador em: https://herd.laravel.com")
        print("2. Instale o aplicativo. Ele configura o PHP 8.4 automaticamente em 10 segundos.")
        print("3. Reabra o terminal e execute este script novamente.")
        print("--------------------------------------------------")
        print("Opção 2: Via Homebrew (Terminal)")
        print("1. Instale o Homebrew rodando o comando no seu terminal:")
        print("   /bin/bash -c \"$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)\"")
        print("2. Em seguida, instale o PHP rodando:")
        print("   brew install php")
        print("3. Execute este script novamente.")
        print("--------------------------------------------------")
        print("\nNota: Se preferir colocar direto em produção, este projeto")
        print("é 100% compatível com Plesk e rodará imediatamente ao ser hospedado.")

if __name__ == "__main__":
    main()
