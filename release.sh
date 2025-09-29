#!/bin/bash

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}🚀 Начинаем процесс создания релиза${NC}"

# Проверяем наличие composer.json
if [ ! -f "composer.json" ]; then
    echo -e "${RED}❌ Файл composer.json не найден!${NC}"
    exit 1
fi

# Извлекаем версию из composer.json
VERSION=$(jq -r '.version' composer.json)

if [ -z "$VERSION" ]; then
    echo -e "${RED}❌ Не удалось извлечь версию из composer.json${NC}"
    exit 1
fi

echo -e "${YELLOW}📦 Текущая версия в composer.json: ${VERSION}${NC}"

# Проверяем, есть ли несохраненные изменения
if ! git diff-index --quiet HEAD -- || ! git diff --cached --quiet; then
    echo -e "${YELLOW}⚠️  Есть несохраненные изменения. Коммитим все файлы...${NC}"
    git add -A
    git commit -m "chore: bump version to ${VERSION} and prepare release"
    echo -e "${GREEN}✅ Все изменения закоммичены${NC}"
else
    echo -e "${GREEN}✅ Нет несохраненных изменений${NC}"
fi

# Получаем текущую ветку
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
echo -e "${YELLOW}🌿 Текущая ветка: ${CURRENT_BRANCH}${NC}"

# Создаем тег
TAG_NAME="v${VERSION}"
echo -e "${YELLOW}🏷️  Создаем тег ${TAG_NAME}...${NC}"

# Проверяем, существует ли уже такой тег
if git rev-parse "$TAG_NAME" >/dev/null 2>&1; then
    echo -e "${RED}❌ Тег ${TAG_NAME} уже существует!${NC}"
    echo -e "${YELLOW}Удалить существующий тег и создать новый? (y/n)${NC}"
    read -r response
    if [[ "$response" =~ ^[Yy]$ ]]; then
        git tag -d "$TAG_NAME"
        git push origin :refs/tags/"$TAG_NAME" 2>/dev/null || true
        echo -e "${GREEN}✅ Старый тег удален${NC}"
    else
        echo -e "${RED}❌ Отменено пользователем${NC}"
        exit 1
    fi
fi

git tag -a "$TAG_NAME" -m "Release version ${VERSION}"
echo -e "${GREEN}✅ Тег ${TAG_NAME} создан${NC}"

# Пушим изменения
echo -e "${YELLOW}📤 Отправляем изменения на удаленный репозиторий...${NC}"

# Если на feature ветке
if [[ "$CURRENT_BRANCH" != "main" ]] && [[ "$CURRENT_BRANCH" != "master" ]]; then
    echo -e "${YELLOW}📤 Пушим ветку ${CURRENT_BRANCH}...${NC}"
    git push origin "$CURRENT_BRANCH"
    echo -e "${GREEN}✅ Ветка ${CURRENT_BRANCH} запушена${NC}"

    echo -e "${YELLOW}⚠️  Вы находитесь на ветке ${CURRENT_BRANCH}. Хотите переключиться на main и смержить? (y/n)${NC}"
    read -r response
    if [[ "$response" =~ ^[Yy]$ ]]; then
        # Сохраняем текущие изменения перед переключением
        if ! git diff-index --quiet HEAD -- || ! git diff --cached --quiet; then
            echo -e "${YELLOW}📦 Сохраняем локальные изменения...${NC}"
            git add -A
            git commit -m "chore: save local changes before switching branches"
        fi

        git checkout main
        git merge "$CURRENT_BRANCH"
        git push origin main
        echo -e "${GREEN}✅ Изменения смержены в main и запушены${NC}"
    fi
else
    git push origin "$CURRENT_BRANCH"
    echo -e "${GREEN}✅ Ветка ${CURRENT_BRANCH} запушена${NC}"
fi

# Пушим тег
echo -e "${YELLOW}📤 Пушим тег ${TAG_NAME}...${NC}"
git push origin "$TAG_NAME"
echo -e "${GREEN}✅ Тег ${TAG_NAME} запушен${NC}"

echo -e "${GREEN}🎉 Релиз ${VERSION} успешно создан!${NC}"
echo -e "${YELLOW}📦 Packagist автоматически обновится через webhook${NC}"
