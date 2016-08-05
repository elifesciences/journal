elifePipeline {
    stage 'Checkout'
    checkout scm
    def commit = elifeGitRevision()

    stage 'Project tests'
    lock('journal--ci') {
        builderDeployRevision 'journal--ci', commit
        builderProjectTests 'journal--ci', '/srv/journal'
        def phpunitTestArtifact = "${env.BUILD_TAG}.phpunit.xml"
        builderTestArtifact phpunitTestArtifact, 'journal--ci', '/srv/journal/build/phpunit.xml'
        def behatTestArtifact = "${env.BUILD_TAG}.behat.xml"
        builderTestArtifact behatTestArtifact, 'journal--ci', '/srv/journal/build/behat.xml'
        elifeVerifyJunitXml phpunitTestArtifact
        elifeVerifyJunitXml behatTestArtifact
    }

    elifeMainlineOnly {
        stage 'Deploy on end2end'
        builderDeployRevision 'journal--end2end', commit
        builderSmokeTests 'journal--end2end', '/srv/journal'

        stage 'Deploy on demo'
        builderDeployRevision 'journal--demo', commit
        builderSmokeTests 'journal--demo', '/srv/journal'

        stage 'Approval'
        elifeGitMoveToBranch commit, 'approved'

        // this should be done by another pipeline prod-journal when we decide to go into prod
        stage 'Not production yet'
        elifeGitMoveToBranch commit, 'master'
        //builderDeployRevision 'journal--prod', commit
        //builderSmokeTests 'journal--prod', '/srv/journal'
    }
}
