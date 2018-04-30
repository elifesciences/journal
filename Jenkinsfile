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
                sh "IMAGE_TAG=${commit} docker-compose -f docker-compose.yml -f docker-compose.ci.yml build"
            }

            stage 'Project tests', {
                try {
                    sh "docker run --name journal_phpunit_${commit} elifesciences/journal_ci:${commit} vendor/bin/phpunit --log-junit build/ci/phpunit.xml"
                    sh "docker cp journal_phpunit_${commit}:/srv/journal/build/ci/phpunit.xml build"
                    step([$class: "JUnitResultArchiver", testResults: 'build/phpunit.xml'])
                } finally {
                    sh "docker rm journal_phpunit_${commit}"
                }

                try {
                    sh "IMAGE_TAG=${commit} docker-compose -f docker-compose.yml -f docker-compose.ci.yml up -d cli fpm web"
                } finally {
                    sh 'docker-compose -f docker-compose.yml -f docker-compose.ci.yml down'
                }
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
