# ስርዓት ምሕደራ ማሕበር ኦርቶዶክስ ኤርትራውያን ቱሉዝ

# Gestion de la Communauté Orthodoxe Érythréenne de Toulouse

## ብዛዕባ እዚ ፕሮጀክት \| À propos du projet

**ትግርኛ (ኤርትራ)**

እዚ ፕሮጀክት እዚ፡ ንምሕደራ ስራሕቲ ማሕበር ኦርቶዶክስ ኤርትራውያን ቱሉዝ ዝተዳለወ ዌብ መተግበሪ እዩ። ኣባላት፡
ኣበርክቶታት፡ ገንዘባዊ ስራሕቲ፡ ሎጂስቲክስን ካልኦት ናይ ማሕበር ስራሕቲን ብሓደ ቦታ ንምምሕዳር ይሕግዝ።

**Français**

Cette application web facilite la gestion administrative de la
Communauté Orthodoxe Érythréenne de Toulouse. Elle centralise les
informations et les opérations liées aux membres, aux cotisations, aux
finances, à la logistique et aux autres activités de la communauté.

## ቀንዲ ተግባራት \| Fonctionnalités principales

**ትግርኛ (ኤርትራ)**

-   መዝገብ ኣባላት ምምሕዳር፡ ምውሳኽ፡ ምእራም፡ ምድላይን ኩነታት ኣባላት ምክትታልን።
-   ብመሰረት ሓላፍነት ተጠቀምቲ ፍቓድ ምሃብ።
-   ናይ ለውጢ ታሪኽ ምሓዝ።
-   ብፈረንሳን ትግርኛን ዝሰርሕ በይነገጽ።
-   ንኣበርክቶታት፡ ገንዘባዊ መዝገባት፡ ደረሰኛታት፡ ወጻኢታት፡ ሎጂስቲክስን ንጥፈታትን ዝምልከቱ ናይ ዳታቤዝ
    መዋቕራት።
-   ብዓመትን ብቅደም ተኸተልን ዝሰርሕ ፍሉይ መለለዪ ቁጽሪ ኣባላት።

**Français**

-   Gestion du registre des membres : création, modification, recherche
    et suivi du statut.
-   Gestion des rôles et des autorisations selon les responsabilités des
    utilisateurs.
-   Historique des modifications.
-   Interface disponible en français et en tigrigna.
-   Structures de données pour les cotisations, les écritures
    financières, les reçus, les dépenses, la logistique et les
    événements.
-   Références uniques des membres, organisées par année et par
    séquence.

**Périmètre fonctionnel :** la présence de tables ou de migrations dans
la base ne signifie pas que chaque écran ou processus correspondant est
terminé. Vérifiez les fonctionnalités dans l'application avant de les
considérer comme opérationnelles.

**ደረጃ ናይ ስራሕ፦** ኣብ ዳታቤዝ ሰንጠረዥ ወይ ሚግሬሽን ምህላዉ፡ እቲ ዝምልከቶ ተግባር ወይ ገጽ ተዛዚሙ
ማለት ኣይኮነን። ከም ዝሰርሕ ቅድሚ ምቕባልኩም ኣብቲ መተግበሪ ፈትሽዎ።

## ቴክኖሎጂ \| Technologies

-   Backend : PHP, Laravel
-   Base de données : MySQL ou MariaDB
-   Frontend : Blade, CSS, JavaScript et Vite
-   Dépendances PHP : Composer
-   Dépendances frontend : npm

## ቅድመ ረቛሒታት \| Prérequis

**ትግርኛ (ኤርትራ)**

ኣብ ኮምፒተርኩም PHP ምስ Laravel ዝሰማማዕ ስሪት፡ Composer፡ MySQL ወይ MariaDB፡
Node.js፡ npmን Gitን ክህልዉ ኣለዎም።

**Français**

Installez PHP dans une version compatible avec Laravel, Composer, MySQL
ou MariaDB, Node.js, npm et Git.

## ምጅማር \| Démarrage rapide

Consultez [`docs/SETUP.md`](docs/SETUP.md) pour les instructions
détaillées en tigrigna érythréen et en français.

``` bash
composer install
npm install
```

Créez `.env` à partir de `.env.example`, configurez la base de données
et les paramètres administrateur, puis exécutez :

``` bash
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

## ምሕላው ሓበሬታን ድሕነትን \| Protection des données et sécurité

**ትግርኛ (ኤርትራ)**

-   `.env` ኣብ Git ወይ GitHub ኣይትስቀሉ።
-   ናይ ሓቂ ፓስዎርድ፡ API keys ወይ ምስጢራዊ ሓበሬታ ኣብ ኮድ ኣይትጽሓፉ።
-   ናይ ኣባላት መዝገብ፡ ደረሰኛታት፡ ገንዘባዊ ሰነዳትን ብሕታዊ ፋይላትን ኣብ GitHub ኣይትስቀሉ።
-   ንነፍሲ ወከፍ ኣካውንት ፍሉይን ጽኑዕን ፓስዎርድ ተጠቐሙ።
-   ቅድሚ ምልቃቕ ስርዓት፡ ባክኣፕን መርመራ ድሕነትን ግበሩ።

**Français**

-   Ne versionnez jamais `.env`.
-   Ne placez aucun mot de passe réel, jeton API ou secret dans le code.
-   Ne publiez pas le registre des membres, les reçus, les documents
    financiers ni les autres fichiers confidentiels sur GitHub.
-   Utilisez un mot de passe unique et robuste pour chaque compte.
-   Avant la mise en production, prévoyez des sauvegardes et des
    contrôles de sécurité.

## ቋንቋ \| Langues

L'application vise une interface en **français** et en **tigrigna
érythréen**. Le tigrigna utilisé dans ce projet doit suivre l'usage
écrit érythréen et ne doit pas être remplacé automatiquement par des
formulations propres à l'usage de la région du Tigray en Éthiopie.

## Licence

Aucune licence spécifique du projet n'est définie dans cette
documentation.
