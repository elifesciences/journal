elifePipeline {
    def commit
    DockerImage image
    stage 'Checkout', {
        checkout scm
        commit = elifeGitRevision()
    }

    node('containers-jenkins-plugin') {
        stage 'Build images', {
            checkout scm
            commit = elifeGitRevision()
            commitShort = elifeGitRevision().substring(0, 8)
            branch = sh(script: 'git rev-parse --abbrev-ref HEAD', returnStdout: true).trim()
            timestamp = sh(script: 'date --utc +%Y%m%d.%H%M', returnStdout: true).trim()
            sh "find build/critical-css -name '*.css' -type f -delete"
            dockerComposeBuild commit
        }

        stage 'Project tests', {
            sh "IMAGE_TAG=${commit} docker-compose -f docker-compose.yml -f docker-compose.ci.yml up -d"
            dockerComposeProjectTestsParallel('journal', commit, [
                'phpunit': '/srv/journal/build/ci/phpunit/*.xml',
            ])

            dockerComposeSmokeTests(commit, [
                'services': [
                    'app': './smoke_tests.sh',
                ],
                'blackbox': [
                    './smoke_tests.sh localhost 8080',
                ]
            ])
        }

        stage 'Generate critical CSS', {
            try {
                sh "IMAGE_TAG=${commit} docker-compose -f docker-compose.yml -f docker-compose.ci.yml run --name=journal_critical_css critical_css"
                sh "docker cp journal_critical_css:build/critical-css/. build/critical-css/"
            } finally {
                sh "docker cp journal_app_1:/srv/journal/var/logs/demo.json.log build/demo.json.log"
                archiveArtifacts artifacts: "build/demo.json.log", fingerprint: true
                sh "docker-compose -f docker-compose.yml -f docker-compose.ci.yml rm -v --stop --force"
            }
        }

        stage 'Rebuild app image', {
            sh "IMAGE_TAG=${commit} docker-compose -f docker-compose.yml -f docker-compose.ci.yml build app"
        }

        stage 'Push app image', {
            image = DockerImage.elifesciences(this, "journal", commit)

            image.tag("${branch}-${commitShort}-${timestamp}").push()

            elifePullRequestOnly {
                def branchName = env.CHANGE_BRANCH
                def tagName = branchName.replaceAll("/", "_")
                image.tag(tagName).push()
            }

            elifeMainlineOnly {
                image.push()
            }
        }
    }

    elifeMainlineOnly {
        stage 'Deploy on continuumtest, continuumtestpreview', {
            def deployments = [
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
            node('containers-jenkins-plugin') {
                image.pull().tag('approved').push()
            }
        }
    }
}
