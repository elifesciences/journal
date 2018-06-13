elifePipeline {
    def commit
    stage 'Checkout', {
        checkout scm
        commit = elifeGitRevision()
    }

    elifeOnNode(
        {
            stage 'Build images', {
                checkout scm
                dockerComposeBuild commit
            }

            stage 'Project tests', {
                sh "IMAGE_TAG=${commit} docker-compose -f docker-compose.yml -f docker-compose.ci.yml up -d"
                dockerComposeProjectTestsParallel('journal', commit, [
                    'phpunit': '/srv/journal/build/ci/phpunit/*.xml',
                    'behat': '/srv/journal/build/ci/behat/*.xml'
                ])

                dockerComposeSmokeTests('profiles', commit, [
                    'scripts': [
                        'cli': './smoke_tests_cli.sh',
                        'fpm': './smoke_tests_fpm.sh',
                    ],
                ])

                sh "./smoke_tests.sh localhost 8080"
            }
        },
        'containers--medium'
    )

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
