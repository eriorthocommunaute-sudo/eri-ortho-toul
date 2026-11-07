# ናይ ከባቢ ምድላው መምርሒ

# Guide d'installation locale

## 1. ቅድመ ረቛሒታት \| Prérequis

**ትግርኛ (ኤርትራ)**

ቅድሚ ምጅማርኩም እዞም መሳርሒታት ኣብ ኮምፒተርኩም ኣዳልዉ፦ PHP ምስ Laravel ዝሰማማዕ ስሪት፡
Composer፡ MySQL ወይ MariaDB፡ Node.js፡ npmን Gitን።

**Français**

Avant l'installation, préparez PHP dans une version compatible avec
Laravel, Composer, MySQL ou MariaDB, Node.js, npm et Git.

## 2. ኮድ ምውራድን ዲፐንደንሲታት ምትካልን \| Récupérer le projet et installer les dépendances

``` bash
git clone <URL_DU_DEPOT>
cd <DOSSIER_DU_PROJET>
composer install
npm install
```

**ትግርኛ (ኤርትራ)**

እቲ ናይ Git ሊንክ ብናይ ግልኹም ዕቃበ ዝርከብ URL ተክኡ። እቲ ፕሮጀክት ምስ ወረደ፡ ናይ PHPን
JavaScriptን ዲፐንደንሲታት ተክሉ።

**Français**

Remplacez l'URL et le nom du dossier par les valeurs de votre dépôt.
Installez ensuite les dépendances PHP et JavaScript.

## 3. ፋይል `.env` ምድላው \| Configurer le fichier `.env`

``` powershell
Copy-Item .env.example .env
```

**ትግርኛ (ኤርትራ)**

1.  ካብ `.env.example` ተበጊስኩም `.env` ፋይል ፍጠሩ።
2.  ናይ ዳታቤዝ ስም፡ ተጠቃሚ ስምን ፓስዎርድን ብናይ ከባቢኹም ክብርታት ቀይሩ።
3.  ናይ መጀመርታ ኣድሚን ስም፡ ኢመይልን ጽኑዕ ፓስዎርድን ኣዳልዉ።
4.  `.env` ኣብ Git ከም ዘይኣቱ ኣረጋግጹ።

**Français**

1.  Créez `.env` à partir de `.env.example`.
2.  Renseignez le nom de la base, l'utilisateur et le mot de passe de
    votre environnement.
3.  Configurez le nom, l'adresse e-mail et un mot de passe robuste pour
    le premier administrateur.
4.  Vérifiez que `.env` reste exclu de Git.

Exemple de paramètres à adapter dans `.env` :

``` dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nom_de_votre_base
DB_USERNAME=votre_utilisateur
DB_PASSWORD=votre_mot_de_passe

INITIAL_ADMIN_NAME=Administrateur
INITIAL_ADMIN_EMAIL=admin@example.org
INITIAL_ADMIN_PASSWORD=remplacer_par_un_secret_unique
```

**Important :** n'utilisez pas les valeurs d'exemple en production. Ne
partagez jamais le contenu réel de `.env`.

## 4. ቁልፊ ምፍጣርን ዳታቤዝ ምድላውን \| Générer la clé et préparer la base

``` bash
php artisan key:generate
php artisan migrate --seed
```

**ትግርኛ (ኤርትራ)**

እታ ቀዳመይቲ ትእዛዝ ናይ Laravel መፍትሕ ትፈጥር። እታ ካልኣይቲ ትእዛዝ ናይ ዳታቤዝ ሰንጠረዣት ትፈጥርን
ናይ መጀመርታ ሓበሬታ ትኣቱን። እቲ ዳታቤዝ ንመጀመርታ ግዜ ክትጥቀሙሉ እንተኾይኑ፡ ቅድሚ ምጅማርኩም ናይ ኣድሚን
ፓስዎርድ ኣብ `.env` ኣዳልዉ።

**Français**

La première commande génère la clé Laravel. La seconde exécute les
migrations et les seeders. Pour une nouvelle installation, configurez le
mot de passe initial de l'administrateur dans `.env` avant de lancer le
seeder.

> **Attention :** le seeder utilise `firstOrCreate` pour le compte
> initial. S'il trouve déjà un compte avec la même adresse e-mail, il ne
> remplace pas automatiquement son nom ni son mot de passe. Une nouvelle
> exécution de `db:seed` ne réinitialise donc pas un compte existant.

## 5. ናይ frontend ፋይላት ምድላው \| Compiler les ressources frontend

``` bash
npm run build
```

Pour travailler en mode développement :

``` bash
npm run dev
```

## 6. መተግበሪ ምጅማር \| Lancer l'application

``` bash
php artisan serve
```

ኣብ መርበብ ሓበሬታ እቲ Artisan ዘርእየኩም ናይ ከባቢ ኣድራሻ ክፈቱ። ብልሙድ
`http://127.0.0.1:8000` እዩ።

Ouvrez l'adresse locale affichée par Artisan, généralement
`http://127.0.0.1:8000`.

## 7. ተኸላ ምርግጋጽ \| Vérifier l'installation

``` bash
php artisan about
php artisan migrate:status
php artisan test
```

**ትግርኛ (ኤርትራ)**

እዞም ትእዛዛት ሓፈሻዊ ሓበሬታ Laravel፡ ኩነታት ሚግሬሽንን ውጽኢት ፈተናታትን ንምርግጋጽ ይሕግዙ። ፈተና
እንተ ፈሺሉ፡ ቅድሚ ምቕጻልኩም እቲ ጸገም መርምሩ።

**Français**

Ces commandes contrôlent la configuration Laravel, l'état des migrations
et les tests automatisés. En cas d'échec, examinez l'erreur avant de
poursuivre.

## 8. ብሕታዊ ሓበሬታን ድሕነትን \| Données privées et sécurité

**ትግርኛ (ኤርትራ)**

ናይ ማሕበር ኣባላት ሓበሬታ፡ ገንዘባዊ ሰነዳት፡ ደረሰኛታትን ካልእ ብሕታዊ ፋይላትን ኣብ GitHub ኣይትስቀሉ።
ኣብዚ ፕሮጀክት እቲ ናይ `storage/app` ፋይላት ካብ Git ክውገዱ ተዳልዮም ኣለዉ። ቅድሚ ነፍሲ ወከፍ
ምልኣኽ ናብ GitHub ኩሉ ግዜ ምርግጋጽ ግበሩ።

**Français**

Ne publiez jamais les données des membres, les documents financiers, les
reçus ou les fichiers confidentiels sur GitHub. Les fichiers de
`storage/app` sont configurés pour rester hors de Git. Vérifiez toujours
les exclusions avant chaque commit et chaque push.

## 9. ሓገዝ \| Assistance

ኣብ ግዜ ምትካል ጸገም እንተ ኣጋጢሙኩም፡ እቲ ሙሉእ መልእኽቲ ጌጋ ሓዙ። ፓስዎርድ፡ API key ወይ ካልእ
ምስጢራዊ ሓበሬታ ኣይተካትቱ።

En cas de problème, conservez le message d'erreur complet, mais retirez
les mots de passe, clés API et autres secrets avant de le partager.
