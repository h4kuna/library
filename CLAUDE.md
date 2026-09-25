# h4kuna

Přehled balíčků, které udržuji, je v `README.md`. Samotné balíčky jsou v `repositories/`, každý je samostatný git repozitář a v tomto repu jsou ignorované.

## Docker prostředí

Testy, PHPStan i composer všech balíčků se pouští v PHP kontejneru z `docker-compose.yml` v tomto adresáři. Lokálně nic neinstaluj ani nespouštěj, vždy přes kontejner:

```bash
docker compose up -d
docker compose exec php sh -c 'cd <balíček> && composer install'
docker compose exec php sh -c 'cd <balíček> && composer tests'
docker compose exec php sh -c 'cd <balíček> && composer stan'
```

- Do kontejneru je namountovaný jen `repositories/` jako `/app`, každý balíček je tedy v `/app/<balíček>`.
- Kontejner běží jako root, Docker je rootless, takže vytvořené soubory patří uživateli na hostu. Do compose nepřidávej `user:`.
- Composer má domov v `var/composer` (cache, `auth.json`), adresář `var/` je v `.gitignore`.
- Chybí-li v image PHP rozšíření, doplň ho do `Dockerfile` a přebuilduj přes `docker compose up -d --build`.

## Commity

Ve všech balíčcích používej [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/): `typ(scope): popis`, anglicky, imperativ. Typy `feat`, `fix`, `refactor`, `test`, `build`, `docs`, `chore`. Scope je adresář v `src/` malými písmeny (`basic`, `iterators`, `collection`, ...). Změny rozděl do commitů po logických celcích, jeden commit může obsahovat i více souborů, ale jen jednu věc. Bez atribuce Claude.
