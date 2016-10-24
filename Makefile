COMPONENTS=authentication authorization configure core cryptography database dependency-injection error events logging network o-authentication routing utility
DEFAULT_BRANCH=master
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)

help:
	@echo ' _____           _ _____  _    _ _____  '
	@echo '|  __ \         | |  __ \| |  | |  __ \ '
	@echo '| |__) |__ _  __| | |__) | |__| | |__) |'
	@echo '|  _  // _` |/ _` |  ___/|  __  |  ___/ '
	@echo '| | \ \ (_| | (_| | |    | |  | | |     '
	@echo '|_|  \_\__,_|\__,_|_|    |_|  |_|_|     '
	@echo ""
	@echo ""
	@echo "release"
	@echo "  Create a new release of RadPHP. Requires the VERSION and GPG_KEY_ID."
	@echo ""
	@echo "components"
	@echo "  Split each of the public namespaces into separate repos and push the to Github."
	@echo ""

guard-%:
	@ if [ "${${*}}" = "" ]; then \
		echo "Environment variable $* not set"; \
		exit 1; \
	fi

components: $(foreach COMPONENT, $(COMPONENTS), component-$(COMPONENT))
components-tag: $(foreach COMPONENT, $(COMPONENTS), tag-component-$(COMPONENT))

component-%:
	$(eval BRANCH_NAME=subtree-$*)

	@git checkout $(DEFAULT_BRANCH) > /dev/null
	@echo 'Split "$*" into "subtree-$*" branch'
	git subtree split --prefix=src/$(shell php -r "echo str_replace('-', '', ucwords('$*', '-'));") -b $(BRANCH_NAME)
	@echo ''

	@echo 'Add remote identified by "$*"'
	@if [ "`git config remote.$*.url`" = "" ]; then\
		git remote add $* git@github.com:RadPHP/$*.git 2> /dev/null;\
	fi

	git push $* $(BRANCH_NAME):$(DEFAULT_BRANCH)
	@git checkout $(CURRENT_BRANCH) > /dev/null
	@echo ''
	@echo ''

tag-component-%: guard-VERSION guard-GPG_KEY_ID component-%
	$(eval BRANCH_NAME=subtree-$*)
	git checkout $(BRANCH_NAME)

	@echo "Creating tag for the $* component on last commit of subtree"
	git tag -s -u $(GPG_KEY_ID) -m "$(shell php -r "echo str_replace('-', '', ucwords('$*', '-'));") version $(VERSION)" v$(VERSION) $(BRANCH_NAME)

	# Push new tag on remote
	git push $* --tags

	# Delete tag to create on other subtree
	git tag -d v$(VERSION)

	@git checkout $(DEFAULT_BRANCH) > /dev/null

tag-release: guard-VERSION
	@echo "Tagging $(VERSION)"
	git tag -s -u $(GPG_KEY_ID) v$(VERSION) -m "RadPHP version $(VERSION)"
	git push origin
	git push origin --tags

release: guard-VERSION guard-GPG_KEY_ID components-tag tag-release

generate-api:
	$(eval REPOSITORY=https://${GH_TOKEN}@github.com/radphp/radphp.git)
	$(eval API_BRANCH=gh-pages)
	$(eval BUILD_DIR=./gh-pages)

	curl -SLO http://www.apigen.org/apigen.phar
	git clone -q $(REPOSITORY) $(BUILD_DIR) --branch $(API_BRANCH) --depth 1 > /dev/null
	yes | php apigen.phar generate -s ./src -d $(BUILD_DIR)

	cd $(BUILD_DIR) || exit 1 \
	&& git remote set-url origin https://${GH_TOKEN}@github.com/radphp/radphp.git \
	&& git config user.email "m.abdolirad@gmail.com" \
	&& git config user.name "Mohammad Abdoli Rad" \
	&& git add . \
	&& git commit -m "Generate API" \
	&& git push origin $(API_BRANCH) -fq > /dev/null