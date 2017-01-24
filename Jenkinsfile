elifePipeline {
    def commit
    stage 'Checkout', {
        checkout scm
        commit = elifeGitRevision()
    }

    stage 'Project tests', {
        lock('journal--ci') {
            builderDeployRevision 'journal--ci', commit
            builderProjectTests 'journal--ci', '/srv/journal', ['/srv/journal/build/phpunit.xml', '/srv/journal/build/behat.xml']
        }
    }

    elifeMainlineOnly {
        stage 'End2end tests', {
            elifeEnd2EndTest {
                builderDeployRevision 'journal--end2end', commit
                builderSmokeTests 'journal--end2end', '/srv/journal'
            }
        }

        stage 'Deploy on demo', {
            builderDeployRevision 'journal--demo', commit
            builderSmokeTests 'journal--demo', '/srv/journal'
        }

        stage 'Approval', {
            elifeGitMoveToBranch commit, 'approved'
        }
    }
}
