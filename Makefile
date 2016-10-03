all: mcstat minecraft_users_

mcstat: src/bin/mcstat.php
	@echo '== Building mcstat =='
	@echo ' - Adding shebang'
	@echo '#!/usr/bin/env php' > src/bin/mcstat
	@echo '<?php' >> src/bin/mcstat
	@echo ' - Adding script file'
	@sed -e '1,5d' < src/bin/mcstat.php >> src/bin/mcstat
	@echo ' - Adding classes'
	@sed -e '1,4d' < src/php/Common.php >> src/bin/mcstat
	@sed -e '1,2d' < src/php/Format.php >> src/bin/mcstat
	@sed -e '1,2d' < src/php/Query.php >> src/bin/mcstat
	@sed -e '1,4d' < src/php/ServerListPing.php >> src/bin/mcstat
	@sed -e '1,2d' < src/php/Status.php >> src/bin/mcstat
	@echo ' - Setting file permissions'
	@chmod 755 src/bin/mcstat
	@echo 'Done.'

minecraft_users_: src/bin/users.php
	@echo '== Building minecraft_users_ =='
	@echo ' - Adding shebang'
	@echo '#!/usr/bin/env php' > src/bin/minecraft_users_
	@echo '<?php' >> src/bin/minecraft_users_
	@echo ' - Adding script file'
	@sed -e '1,5d' < src/bin/users.php >> src/bin/minecraft_users_
	@echo ' - Adding classes'
	@sed -e '1,4d' < src/php/Common.php >> src/bin/minecraft_users_
	@sed -e '1,2d' < src/php/Format.php >> src/bin/minecraft_users_
	@sed -e '1,2d' < src/php/Query.php >> src/bin/minecraft_users_
	@sed -e '1,4d' < src/php/ServerListPing.php >> src/bin/minecraft_users_
	@sed -e '1,2d' < src/php/Status.php >> src/bin/minecraft_users_
	@echo ' - Setting file permissions'
	@chmod 755 src/bin/minecraft_users_
	@echo 'Done.'

clean:
	@echo '== Cleaning up =='
	@rm -f src/bin/mcstat src/bin/minecraft_users_
	@echo 'Done.'

test: src/tests/unit-tests/bin/testrunner.sh all
	@echo '== Running tests =='
	@./src/tests/unit-tests/bin/testrunner.sh

.PHONY: all clean test
