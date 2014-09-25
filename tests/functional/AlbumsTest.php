<?php

class AlbumsTest extends LocalWebTestCase
{
	public function canGetAlbums(){
        
        $this->client->get('/albums');

        $this->assertEquals(200, $this->client->response->status());
        $this->assertEquals('application/json', $this->client->response['Content-Type']);

        $albums = json_decode($this->client->response->body());

        $this->assertEquals(1, count($albums));
    }

    public function testSongDoesntExist(){
    	$this->client->get('/albums/100');
        $this->assertEquals(404, $this->client->response->status());
        $this->assertEquals('application/json', $this->client->response['Content-Type']);
    }

    public function testSongExists(){

    	$this->client->get('/albums/1');
        $this->assertEquals(200, $this->client->response->status());
        $this->assertEquals('application/json', $this->client->response['Content-Type']);

        $albumData = json_decode($this->client->response->body());

        $this->assertSame("Demo Album", $albumData->album->name);
    }
}