elifePipeline {
    def commit
    stage 'Checkout', {
        checkout scm
        commit = elifeGitRevision()
    }

    node('containers-jenkins-plugin') {
        stage 'Build images', {
            checkout scm
            sh "find build/critical-css -name '*.css' -type f -delete"
            sh "touch build/critical-css/default.css"
            dockerComposeBuild commit
        }

        stage 'Project tests', {
            sh "IMAGE_TAG=${commit} docker-compose -f docker-compose.yml -f docker-compose.ci.yml up -d"
            dockerComposeProjectTestsParallel('journal', commit, [
                'phpunit': '/srv/journal/build/ci/phpunit/*.xml',
                'behat': '/srv/journal/build/ci/behat/*.xml'
            ])

            dockerComposeSmokeTests(commit, [
                'services': [
                    'cli': './smoke_tests_cli.sh',
                    'fpm': './smoke_tests_fpm.sh',
                ],
                'blackbox': [
                    './smoke_tests.sh localhost 8080',
                ]
            ])
        }

        stage 'Generate critical CSS', {
            sh "IMAGE_TAG=${commit} docker-compose -f docker-compose.yml -f docker-compose.ci.yml up -d critical_css"
            sh "IMAGE_TAG=${commit} docker-compose -f docker-compose.yml -f docker-compose.ci.yml exec -T critical_css node_modules/.bin/gulp critical-css:generate"
            sh "IMAGE_TAG=${commit} docker-compose -f docker-compose.yml -f docker-compose.ci.yml exec -T critical_css ./check_critical_css.sh"
            sh "docker cp journal_critical_css_1:build/critical-css/. build/critical-css/"
            sh "docker-compose -f docker-compose.yml -f docker-compose.ci.yml down -v"
        }

        stage 'Rebuild FPM image', {
            sh "IMAGE_TAG=${commit} docker-compose -f docker-compose.yml -f docker-compose.ci.yml build fpm"
        }
    }

    elifeMainlineOnly {
        stage 'End2end tests', {
            elifeSpectrum(
                deploy: [
                    stackname: 'journal--end2end',
                    revision: commit,
                    folder: '/srv/journal',
                    concurrency: 'blue-green'
                ],
                marker: 'journal'
            )
        }

        stage 'Deploy on demo, continuumtest', {
            def deployments = [
                demo: {
                    lock('journal--demo') {
                        builderDeployRevision 'journal--demo', commit
                        builderSmokeTests 'journal--demo', '/srv/journal'
                    }
                },
                continuumtest: {
                    lock('journal--continuumtest') {
                        builderDeployRevision 'journal--continuumtest', commit
                        builderSmokeTests 'journal--continuumtest', '/srv/journal'
                    }
                },
                continuumtestpreview: {
                    lock('journal--continuumtestpreview') {
                        builderDeployRevision 'journal--continuumtestpreview', commit
                        builderSmokeTests 'journal--continuumtestpreview', '/srv/journal'
                    }
                }
            ]
            parallel deployments
        }

        stage 'Approval', {
            elifeGitMoveToBranch commit, 'approved'
        }
    }
}
