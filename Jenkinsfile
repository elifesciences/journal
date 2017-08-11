elifePipeline {
    def commit
    stage 'Checkout', {
        checkout scm
        commit = elifeGitRevision()
    }

    stage 'Project tests', {
        lock('journal--ci') {
            builderDeployRevision 'journal--ci', commit
            builderProjectTests 'journal--ci', '/srv/journal', ['/srv/journal/build/phpunit.xml', '/srv/journal/build/behat.xml'], ['smoke', 'project']
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
                ]
            )
        }

        stage 'Deploy on demo', {
            builderDeployRevision 'journal--demo', commit
            builderSmokeTests 'journal--demo', '/srv/journal'
        }

        stage 'Deploy on continuumtest', {
            builderDeployRevision 'journal--continuumtest', commit
            builderSmokeTests 'journal--continuumtest', '/srv/journal'
        }

        stage 'Approval', {
            elifeGitMoveToBranch commit, 'approved'
        }
    }
}
